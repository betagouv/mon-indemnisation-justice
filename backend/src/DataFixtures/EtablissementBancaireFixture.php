<?php

namespace MonIndemnisationJustice\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use MonIndemnisationJustice\Entity\EtablissementBancaire;

class EtablissementBancaireFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        foreach ([
            '30004' => 'BNP Paribas',
            '30003' => 'Société générale',
            '30002' => 'Crédit lyonnais',
            '20041' => 'La Banque Postale',
            '18206' => "Caisse régionale de crédit agricole mutuel de Paris et d'Ile-de-France",
            '17515' => "Caisse d'épargne et de prévoyance Ile-de-France",
            '30066' => 'Crédit industriel et commercial - CIC',
            '40618' => 'Boursorama',
        ] as $codeBanque => $nom) {
            $etablissement = new EtablissementBancaire()
                ->setCodeBanque((string) $codeBanque)
                ->setNom($nom);

            $manager->persist($etablissement);
            $this->addReference("etablissement-bancaire-$codeBanque", $etablissement);
        }

        $manager->flush();
    }
}
