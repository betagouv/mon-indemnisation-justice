<?php

namespace MonIndemnisationJustice\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use MonIndemnisationJustice\Entity\Avocat;
use MonIndemnisationJustice\Entity\Barreau;

/**
 * Un petit jeu de barreaux/avocats déterministe pour les tests (e2e notamment), qui ne dépend pas
 * de l'exécution préalable de `mij:importer:avocats` (import réel du CNB, non lancé en test/CI).
 */
class AvocatFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $barreauParis = new Barreau()->setId('0075')->setNom('PARIS');
        $manager->persist($barreauParis);
        $this->addReference('barreau-paris', $barreauParis);

        $barreauLyon = new Barreau()->setId('0069')->setNom('LYON');
        $manager->persist($barreauLyon);
        $this->addReference('barreau-lyon', $barreauLyon);

        foreach ([
            'michon' => new Avocat()
                ->setNumeroCnbf('123456')
                ->setNom('MICHON')
                ->setPrenom('Jean')
                ->setRaisonSociale('MICHON AVOCAT')
                ->setEmail('jean.michon@avocat.fr')
                ->setBarreau($barreauParis),
            'keran' => new Avocat()
                ->setNumeroCnbf('654321')
                ->setNom('KERAN')
                ->setPrenom('Ray')
                ->setRaisonSociale('KERAN AVOCAT')
                ->setEmail('ray.keran@avocat.fr')
                ->setBarreau($barreauLyon),
        ] as $reference => $avocat) {
            $manager->persist($avocat);
            $this->addReference("avocat-{$reference}", $avocat);
        }

        $manager->flush();
    }
}
