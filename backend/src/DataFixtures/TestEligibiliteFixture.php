<?php

namespace MonIndemnisationJustice\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use MonIndemnisationJustice\Entity\GeoDepartement;
use MonIndemnisationJustice\Entity\QualiteRequerant;
use MonIndemnisationJustice\Entity\Requerant;
use MonIndemnisationJustice\Entity\TestEligibilite;

class TestEligibiliteFixture extends Fixture implements DependentFixtureInterface
{
    public const int ID_EN_XP_INCOMPLET = 1;
    public const int ID_EN_XP_COMPLET = 2;
    public const int ID_HORS_XP = 3;

    public function getDependencies(): array
    {
        return [
            RequerantFixture::class,
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
                'rapportAuLogement' => QualiteRequerant::LOC,
                'aContacteAssurance' => false,
                'aContacteBailleur' => false,
                'requerant' => $this->getReference('requerant-ray', Requerant::class),
                'dateSoumission' => new \DateTime('-7 days'),
            ]),
        ] as $reference => $testEligibilite) {
            $manager->persist($testEligibilite);
            $this->addReference("test-eligibilite-{$reference}", $testEligibilite);
        }

        $manager->flush();
    }
}
