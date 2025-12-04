<?php

namespace MonIndemnisationJustice\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use MonIndemnisationJustice\Entity\Adresse;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Entity\EtatDossier;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Entity\GeoCommune;
use MonIndemnisationJustice\Entity\GeoDepartement;
use MonIndemnisationJustice\Entity\QualiteRequerant;
use MonIndemnisationJustice\Entity\Requerant;
use MonIndemnisationJustice\Entity\TestEligibilite;
use MonIndemnisationJustice\Service\DocumentManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class DossierFixture extends Fixture implements DependentFixtureInterface
{
    protected Generator $faker;
    protected string $dossierRessources;
    protected string $dossierTeleversement;
    protected Filesystem $filesystem;

    /** @var BrisPorte[] */
    private static $REGISTRE_DOSSIERS = [];

    public function __construct(
        protected readonly UserPasswordHasherInterface $passwordHasher,
        #[Autowire(param: 'kernel.project_dir')]
        protected readonly string $kernelDirectory,
        protected readonly DocumentManager $documentManager,
    ) {
        $this->faker = Factory::create('fr_FR');
        $this->dossierTeleversement = $this->kernelDirectory.'/fichiers/test/';
        $this->dossierRessources = $this->kernelDirectory.'/tests/ressources/';
        $this->filesystem = new Filesystem();
    }

    public function getDependencies(): array
    {
        return [
            GeoFixtures::class,
            AgentFixture::class,
            RequerantFixture::class,
            TestEligibiliteFixture::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $finder = new Finder();
        $finder->files()->in($this->dossierTeleversement);

        if ($finder->hasResults()) {
            foreach ($finder as $fichier) {
                $this->filesystem->remove($fichier);
            }
        }

        // Dossiers
        $dossierAFinaliser = (new BrisPorte())
            ->setRequerant($this->getReference('requerant-raquel', Requerant::class))
            ->setAdresse((new Adresse())
                ->setLigne1('12 rue des Oliviers')
                ->setCodePostal('44100'))
            ->setDescriptionRequerant('Porte fracturée tôt ce matin')
            ->setTestEligibilite(
                TestEligibilite::fromArray([
                    'departement' => $this->getReference('departement-bouches-du-rhone', GeoDepartement::class),
                    'estVise' => false,
                    'estHebergeant' => false,
                    'rapportAuLogement' => QualiteRequerant::PRO,
                    'aContacteAssurance' => false,
                    'requerant' => $this->getReference('requerant-raquel', Requerant::class),
                    'dateSoumission' => new \DateTime('-30 seconds'),
                ])
            )
        ;

        $this->addReference('dossier-a-finaliser-nantes', $dossierAFinaliser);

        $manager->persist($dossierAFinaliser);

        $dossierAFinaliser2 = $this->creerDossier(
            $this->getReference('requerant-saint-malo', Requerant::class),
            [
                'rapportAuLogement' => QualiteRequerant::LOC,
                'estVise' => false,
                'estHebergeant' => false,
                'aContacteAssurance' => false,
            ],
            new \DateTimeImmutable('-15 days'),
            EtatDossierType::DOSSIER_A_FINALISER,
        );

        $this->addReference('dossier-a-finaliser-saint-malo', $dossierAFinaliser2);

        $manager->persist($dossierAFinaliser2);

        $dossierAAttribuer = (new BrisPorte())
            ->setReference('BRI/20250410/001')
            ->setDateDepot(\DateTimeImmutable::createFromFormat('Y-m-d', '2025-04-10'))
            ->setDateOperationPJ($this->faker->dateTimeBetween('-100 days', 'now'))
            ->setRequerant($this->getReference('requerant-melun', Requerant::class))
            ->setAdresse(
                (new Adresse())
                    ->setCommune($this->getReference('commune-melun', GeoCommune::class))
                    ->setCodePostal('77000')
                    ->setLigne1($this->faker->streetAddress())
                    ->setLocalite($this->faker->city())
            )
            ->setTestEligibilite(
                $this->getReference('test-eligibilite-melun', TestEligibilite::class)
            )
            ->setQualiteRequerant(QualiteRequerant::BAI)
        ;

        $dossierAAttribuer->setHistoriqueEtats([
            (new EtatDossier())
                ->setEtat(EtatDossierType::DOSSIER_A_FINALISER)
                ->setDateEntree(\DateTimeImmutable::createFromFormat('Y-m-d', '2025-04-10'))
                ->setRequerant($this->getReference('requerant-melun', Requerant::class)),
            (new EtatDossier())
                ->setEtat(EtatDossierType::DOSSIER_A_ATTRIBUER)
                ->setDateEntree(\DateTimeImmutable::createFromFormat('Y-m-d', '2025-04-10'))
                ->setRequerant($this->getReference('requerant-melun', Requerant::class)),
        ]);

        $dossierAAttribuer->ajouterDocument(
            (new Document())
                ->setFilename('dd84b8ed97fd350cf29bbb229a1f330cb9d89f69e47832e51b18abbcffe425e6.pdf')
                ->setOriginalFilename('Attestation POLICE')
                ->setSize(428116)
                ->setType(DocumentType::TYPE_ATTESTATION_INFORMATION)
                ->setAjoutRequerant(true)
                ->setMime('application/pdf')
        );

        $manager->persist($dossierAAttribuer);

        $this->addReference('dossier-a-attribuer-melun', $dossierAAttribuer);

        $dossierAInstruire = $this->creerDossier(
            $this->getReference('requerant-melun', Requerant::class),
            [
                'rapportAuLogement' => QualiteRequerant::BAI,
                'estVise' => true,
            ],
            new \DateTimeImmutable('-18 days'),
            EtatDossierType::DOSSIER_A_INSTRUIRE,
            redacteur: $this->getReference('agent-redacteur', Agent::class)
        )->ajouterDocument($this->creerDocumentDepuisRessource('pieces_jointes/Facture 1.png', DocumentType::TYPE_FACTURE, true));

        $this->addReference('dossier-en-instruction-melun', $dossierAInstruire);

        $manager->persist($dossierAInstruire);

        $dossierEnInstruction = (new BrisPorte())
            ->setReference('BRI/20250103/001')
            ->setDateDepot(\DateTimeImmutable::createFromFormat('Y-m-d', '2025-01-03'))
            ->setRequerant($this->getReference('requerant-ray', Requerant::class))
            ->setRedacteur($this->getReference('agent-redacteur', Agent::class))
            ->setTestEligibilite(
                $this->getReference('test-eligibilite-ray-keran', TestEligibilite::class)
            )
            ->setQualiteRequerant(QualiteRequerant::LOC)
        ;

        $dossierEnInstruction->setHistoriqueEtats([
            (new EtatDossier())
                ->setEtat(EtatDossierType::DOSSIER_A_FINALISER)
                ->setDateEntree(new \DateTimeImmutable('-7 days'))
                ->setRequerant($this->getReference('requerant-ray', Requerant::class)),
            (new EtatDossier())
                ->setEtat(EtatDossierType::DOSSIER_A_ATTRIBUER)
                ->setDateEntree(new \DateTimeImmutable('-6 days'))
                ->setRequerant($this->getReference('requerant-ray', Requerant::class)),
            (new EtatDossier())
                ->setEtat(EtatDossierType::DOSSIER_A_INSTRUIRE)
                ->setDateEntree(new \DateTimeImmutable('-5 days'))
                ->setAgent($this->getReference('agent-attributeur', Agent::class)),
            (new EtatDossier())
                ->setEtat(EtatDossierType::DOSSIER_EN_INSTRUCTION)
                ->setDateEntree(new \DateTimeImmutable('-4 days'))
                ->setAgent($this->getReference('agent-redacteur', Agent::class)),
        ]);

        $manager->persist($dossierEnInstruction);

        $dossierRejetASigner = $this->creerDossier(
            $this->getReference('requerant-saint-malo', Requerant::class),
            [
                'rapportAuLogement' => QualiteRequerant::PRO,
                'estVise' => true,
            ],
            new \DateTimeImmutable('-35 days'),
            EtatDossierType::DOSSIER_KO_A_SIGNER,
            redacteur: $this->getReference('agent-redacteur', Agent::class)
        );

        $this->addReference('dossier-rejet-a-signer-saint-malo', $dossierRejetASigner);

        $manager->persist($dossierRejetASigner);

        $dossierPropositionASigner = $this->creerDossier(
            $this->getReference('requerant-aix-en-provence', Requerant::class),
            [
                'rapportAuLogement' => QualiteRequerant::LOC,
                'estVise' => false,
                'estHebergeant' => true,
            ],
            new \DateTimeImmutable('-37 days'),
            EtatDossierType::DOSSIER_OK_A_SIGNER,
            redacteur: $this->getReference('agent-redacteur', Agent::class)
        )->setPropositionIndemnisation('1167.89');

        $this->addReference('dossier-a-signer-aix-en-provence', $dossierPropositionASigner);

        $manager->persist($dossierPropositionASigner);

        $dossierAVerifier = $this->creerDossier(
            $this->getReference('requerant-melun', Requerant::class),
            [
                'rapportAuLogement' => QualiteRequerant::LOC,
                'estVise' => false,
                'estHebergeant' => false,
                'aContacteAssurance' => true,
                'aContacteBailleur' => true,
            ],
            new \DateTimeImmutable('-35 days'),
            EtatDossierType::DOSSIER_OK_A_VERIFIER,
            redacteur: $this->getReference('agent-redacteur', Agent::class)
        )->setPropositionIndemnisation('2031');

        $this->addReference('dossier-a-verifier-melun', $dossierAVerifier);

        $manager->persist($dossierAVerifier);

        $dossierArreteASigner = $this->creerDossier(
            $this->getReference('requerant-saint-malo', Requerant::class),
            [
                'rapportAuLogement' => QualiteRequerant::BAI,
                'estVise' => false,
                'estHebergeant' => false,
                'aContacteAssurance' => true,
            ],
            new \DateTimeImmutable('-43 days'),
            EtatDossierType::DOSSIER_OK_VERIFIE,
            redacteur: $this->getReference('agent-redacteur', Agent::class)
        )->setPropositionIndemnisation('3084.97');

        $this->addReference('dossier-arrete-a-signer-saint-malo', $dossierArreteASigner);

        $manager->persist($dossierArreteASigner);

        $dossierATransmettre = $this->creerDossier(
            $this->getReference('requerant-ancenis', Requerant::class),
            [
                'rapportAuLogement' => QualiteRequerant::BAI,
                'estVise' => false,
                'estHebergeant' => false,
                'aContacteAssurance' => true,
            ],
            new \DateTimeImmutable('-41 days'),
            EtatDossierType::DOSSIER_OK_A_INDEMNISER,
            redacteur: $this->getReference('agent-redacteur', Agent::class)
        )->setPropositionIndemnisation('774.25');

        $this->addReference('dossier-a-transmettre-ancenis', $dossierATransmettre);

        $manager->persist($dossierATransmettre);

        $dossierEnAttenteIndemnisation = $this->creerDossier(
            $this->getReference('requerant-istres', Requerant::class),
            [
                'rapportAuLogement' => QualiteRequerant::LOC,
                'estVise' => false,
                'estHebergeant' => false,
                'aContacteAssurance' => true,
                'aContacteBailleur' => false,
            ],
            new \DateTimeImmutable('-41 days'),
            EtatDossierType::DOSSIER_OK_EN_ATTENTE_PAIEMENT,
            redacteur: $this->getReference('agent-redacteur', Agent::class)
        )->setPropositionIndemnisation('1912.77');

        $this->addReference('dossier-en-attente-indemnisation-istres', $dossierEnAttenteIndemnisation);

        $manager->persist($dossierEnAttenteIndemnisation);

        $manager->flush();
    }

    /**
     * @return EtatDossierType[]
     */
    public static function historiqueTheorique(EtatDossierType $etat): array
    {
        $etatPrecedent = $etat->etatPrecedent();

        if ($etatPrecedent) {
            return array_merge(self::historiqueTheorique($etatPrecedent), [$etat]);
        }

        return [$etat];
    }

    protected function creerDossier(Requerant $requerant, array $donneesTestEligibilite, \DateTimeInterface $dateCreation, EtatDossierType $etatActuel, ?Adresse $adresse = null, ?Agent $redacteur = null): BrisPorte
    {
        $dossier = (new BrisPorte())
            ->setDateOperationPJ($this->faker->dateTimeBetween('-100 days', 'now'))
            ->setRequerant($requerant)
            ->setRedacteur($redacteur)
            ->setDateCreation($dateCreation)
            ->setAdresse($adresse ?? $requerant->getAdresse())
        ;

        $dossier
            ->setTestEligibilite(
                TestEligibilite::fromArray(
                    array_merge(
                        $donneesTestEligibilite,
                        [
                            'requerant' => $dossier->getRequerant(),
                            'dateSoumission' => $dossier->getDateCreation(),
                        ]
                    ),
                )
            )
            ->setQualiteRequerant($dossier->getTestEligibilite()->rapportAuLogement)
        ;

        foreach (self::historiqueTheorique($etatActuel) as $etat) {
            $historique[] = (new EtatDossier())
                ->setEtat($etat)
                ->setDateEntree(
                    empty($historique)
                        ? $dossier->getDateCreation()
                        : (clone ($historique[count($historique) - 1])->getDate())->modify(sprintf('+ %d minutes', random_int(10, 3 * 24 * 60)))
                )
                ->setRequerant(in_array($etat, [EtatDossierType::DOSSIER_A_FINALISER, EtatDossierType::DOSSIER_A_ATTRIBUER, EtatDossierType::DOSSIER_OK_A_VERIFIER]) ? $dossier->getRequerant() : null)
                ->setAgent($this->getAgentPourEtat($dossier, $etat))
            ;
        }

        $dossier->setHistoriqueEtats($historique);

        if (EtatDossierType::DOSSIER_A_FINALISER !== $etatActuel) {
            $dossier->setReference(
                sprintf(
                    'BRI/%s/%s',
                    $date = $dossier->getEtat(EtatDossierType::DOSSIER_A_ATTRIBUER)->getDate()->format('Ymd'),
                    str_pad(count(self::$REGISTRE_DOSSIERS[$date] ?? []) + 1, 3, '0', STR_PAD_LEFT)
                )
            );
            $dossier->setDateDepot($dossier->getEtat(EtatDossierType::DOSSIER_A_ATTRIBUER)->getDate());

            self::$REGISTRE_DOSSIERS[$date][] = $dossier;
        }

        return $dossier;
    }

    protected function getAgentPourEtat(BrisPorte $dossier, EtatDossierType $etat): ?Agent
    {
        return match ($etat) {
            EtatDossierType::DOSSIER_A_INSTRUIRE => $this->getReference('agent-attributeur', Agent::class),
            EtatDossierType::DOSSIER_OK_A_SIGNER, EtatDossierType::DOSSIER_KO_A_SIGNER, EtatDossierType::DOSSIER_OK_VERIFIE => $dossier->getRedacteur(),
            EtatDossierType::DOSSIER_KO_REJETE, EtatDossierType::DOSSIER_OK_A_APPROUVER, EtatDossierType::DOSSIER_OK_A_INDEMNISER => $this->getReference('agent-validateur', Agent::class),
            default => null
        };
    }

    protected function getCacheKey(): string
    {
        return 'fixture-dossier';
    }

    private function creerDocumentDepuisRessource(string $chemin, DocumentType $type, ?bool $estAjoutRequerant = true): Document
    {
        $cheminFichier = $this->dossierRessources.$chemin;

        return $this->documentManager->enregistrerDocument(
            (new Document())
                ->setOriginalFilename(basename($cheminFichier))
                ->setAjoutRequerant($estAjoutRequerant)
                ->setType($type)
                ->setMime($this->documentManager->calculerTypeMime($cheminFichier)),
            $this->filesystem->readFile($cheminFichier),
        );
    }
}
