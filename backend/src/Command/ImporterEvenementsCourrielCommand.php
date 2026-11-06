<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Command;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use MonIndemnisationJustice\Entity\EnvoiCourriel;
use MonIndemnisationJustice\Entity\EnvoiCourrielEvenement;
use MonIndemnisationJustice\Entity\EnvoiCourrielEvenementType;
use MonIndemnisationJustice\Entity\EnvoiCourrielType;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'mij:importer:evenements_courriel', description: "Importe les évènements de suivi de courriel Brevo à partir d'une fichier d'export de logs")]
/**
 * Commande qui permet d'importer un fichier d'évènements de suivi de courriels exportés depuis la web UI de Brevo.
 *
 * Temporaire, à n'utiliser que lors de la mise en route de la collecte.
 */
class ImporterEvenementsCourrielCommand extends Command
{
    /** @var array<string, EnvoiCourriel> Tableau de suivi des déclarations nouvellement importées */
    protected array $declarationsImportees = [];

    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
        parent::__construct();


    }

    protected function configure(): void
    {
        $this->addArgument('fichier', InputArgument::REQUIRED, 'Chemin vers le fichier');
        $this->addOption('simulation', 's', InputOption::VALUE_NONE, "Simuler l'import uniquement et ne pas insérer de données");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        /** @var string $cheminFichier */
        $cheminFichier = $input->getArgument('fichier');
        $simulation = $input->getOption('simulation');

        if (!is_file($cheminFichier) || !is_readable($cheminFichier)) {
            $io->error("Le fichier $cheminFichier n'existe pas ou n'est pas lisible");

            return Command::FAILURE;
        }

        $fichier = new \SplFileObject($cheminFichier);
        $fichier->setFlags(\SplFileObject::READ_CSV | \SplFileObject::READ_AHEAD | \SplFileObject::SKIP_EMPTY | \SplFileObject::DROP_NEW_LINE);
        $fichier->setCsvControl(separator: ',', enclosure: '"', escape: '');

        $nbDeclarationsTrouvees = 0;
        $nbEvenementsAjoutes = 0;
        $nbLignesLues = 0;

        foreach ($fichier as $numeroLigne => $valeurs) {
            if ($numeroLigne > 0) {
                list($evenement, $date, $objet, $expediteur, $recepteur) = $valeurs;

                // Si l'objet du courriel est celui envoyé lors d'une invitation à déposer suite à une déclaration...
                if ("Mon Indemnisation Justice: vous pouvez faire une demande d'indemnisation" === $objet) {
                    // ... que l'on trouve *une seule* déclaration liée à l'adresse courriel du récepteur...
                    if (null !== ($declaration = $this->chercherDeclaration($recepteur))) {
                        ++$nbDeclarationsTrouvees;
                        /*
                        /** @var EnvoiCourriel $envoiCourriel */
                        $envoiCourriel = null;
                        // ...si on a déjà créé un `EnvoiCourriel` pour cette déclaration alors, on la reprend
                        if (isset($this->declarationsImportees[$declaration->getId()->toString()])) {
                            $envoiCourriel = $this->declarationsImportees[$declaration->getId()->toString()];
                        } else {
                            // sinon, on cherche les envois associés à la déclaration
                            $envois = $this->em->getRepository(EnvoiCourriel::class)->findBy([
                                'declaration' => $declaration->getId(),
                            ]);

                            // Si aucun alors, on peut en créer un sans risque de confusion
                            if (empty($envois)) {
                                $envoiCourriel = new EnvoiCourriel()
                                    ->setType(EnvoiCourrielType::INVITATION_FDO)
                                    ->setDeclaration($declaration)
                                    ->setRecepteur($recepteur);

                                $this->declarationsImportees[$declaration->getId()->toString()] = $envoiCourriel;

                            } elseif (1 === count($envois)) {
                                // Si un seul envoi connu, on considère que c'est le même
                                $envoiCourriel = $envois[0];
                            }
                        }

                        if (null !== $envoiCourriel) {
                            $type = $this->extraireTypeEvenementDepuisLibelle(
                                $evenement
                            );

                            if (null !== $type) {
                                $envoiCourriel->ajouterEvenement(
                                    new EnvoiCourrielEvenement()
                                        ->setType($type)
                                        ->setDate(\DateTimeImmutable::createFromFormat('d-m-Y H:i:s', $date))
                                        ->setCourriel($envoiCourriel)
                                );

                                if (!$simulation) {
                                    $this->em->persist($envoiCourriel);
                                    $this->em->flush();
                                }

                                ++$nbEvenementsAjoutes;
                            }
                        }

                    }
                }

                ++$nbLignesLues;
            }
        }

        $io->success([
            "$nbLignesLues ligne(s) traitée(s)",
            "$nbDeclarationsTrouvees déclaration(s) trouvée(s)",
            "$nbEvenementsAjoutes évènements importé(s)",
        ]);

        if ($simulation) {
            $io->warning('Import simulé, aucune donnée altérée');
        }

        return Command::SUCCESS;
    }

    protected function chercherDeclaration(string $courrielRequerant): ?DeclarationFDOBrisPorte
    {
        return $this->em->getRepository(DeclarationFDOBrisPorte::class)->findParCourrielRequerant($courrielRequerant);
    }

    protected function extraireTypeEvenementDepuisLibelle(string $libelle): ?EnvoiCourrielEvenementType
    {

        return match ($libelle) {
            'Envoyé' => EnvoiCourrielEvenementType::EXPEDIE,
            'Délivré' => EnvoiCourrielEvenementType::RECU,
            'Bloqué', 'Soft bounce', 'Hard bounce' => EnvoiCourrielEvenementType::REJETE,
            '1ère ouverture', 'Ouvert' => EnvoiCourrielEvenementType::OUVERT,
            'Cliqué' => EnvoiCourrielEvenementType::CLIQUE,
            default => null,
        };
    }
}
