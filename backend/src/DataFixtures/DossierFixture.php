<?php

namespace MonIndemnisationJustice\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\EtatDossier;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Entity\GeoDepartement;
use MonIndemnisationJustice\Entity\QualiteRequerant;
use MonIndemnisationJustice\Entity\Requerant;
use MonIndemnisationJustice\Entity\TestEligibilite;

class DossierFixture extends Fixture implements DependentFixtureInterface
{
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
        // Dossiers
        $dossierAFinaliser = (new BrisPorte())
            ->setRequerant($this->getReference('requerant-raquel', Requerant::class))
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

        $manager->persist($dossierAFinaliser);

        $dossierEnInstruction = (new BrisPorte())
            ->setReference('BRI/20250103/001')
            ->setRequerant($this->getReference('requerant-ray', Requerant::class))
            ->setRedacteur($this->getReference('agent-redacteur', Agent::class))
            ->setTestEligibilite(
                $this->getReference('test-eligibilite-ray-keran', TestEligibilite::class)
            )
        ;

        $dossierEnInstruction->setHistoriqueEtats([
            (new EtatDossier())
                ->setEtat(EtatDossierType::DOSSIER_A_FINALISER)
                ->setDateEntree(new \DateTimeImmutable('-7 days'))
                ->setRequerant($this->getReference('requerant-ray', Requerant::class)),
            (new EtatDossier())
                ->setEtat(EtatDossierType::DOSSIER_A_INSTRUIRE)
                ->setDateEntree(new \DateTimeImmutable('-6 days'))
                ->setRequerant($this->getReference('requerant-ray', Requerant::class)),
            (new EtatDossier())
                ->setEtat(EtatDossierType::DOSSIER_EN_INSTRUCTION)
                ->setDateEntree(new \DateTimeImmutable('-4 days'))
                ->setAgent($this->getReference('agent-redacteur', Agent::class)),
        ]);

        $manager->persist($dossierEnInstruction);

        $manager->flush();
    }
}
