<?php

namespace MonIndemnisationJustice\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use MonIndemnisationJustice\Entity\GeoDepartement;
use MonIndemnisationJustice\Entity\RapportAuLogement;
use MonIndemnisationJustice\Entity\TestEligibilite;
use MonIndemnisationJustice\Entity\Usager;

class TestEligibiliteFixture extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            UsagerFixture::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        foreach ([
            'ray-keran' => TestEligibilite::fromArray([
                'departement' => $this->getReference('departement-ille-et-vilaine', GeoDepartement::class),
                // 'description' => 'Porte fracturée tôt ce matin',
                'estVise' => false,
                'estHebergeant' => false,
                'rapportAuLogement' => RapportAuLogement::LOCATAIRE,
                'aContacteAssurance' => false,
                'aContacteBailleur' => false,
                'requerant' => $this->getReference('requerant-ray', Usager::class),
                'dateSoumission' => new \DateTime('-7 days'),
            ]),
            'melun' => TestEligibilite::fromArray([
                'estVise' => false,
                'estHebergeant' => false,
                'rapportAuLogement' => RapportAuLogement::BAILLEUR,
                'aContacteAssurance' => false,
                'requerant' => $this->getReference('requerant-melun', Usager::class),
                'dateSoumission' => \DateTime::createFromFormat('Y-m-d H:i:s', '2025-04-10 13:51:27'),
            ]),
        ] as $reference => $testEligibilite) {
            $manager->persist($testEligibilite);
            $this->addReference("test-eligibilite-{$reference}", $testEligibilite);
        }

        $manager->flush();
    }
}
