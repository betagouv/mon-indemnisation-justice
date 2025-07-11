<?php

namespace MonIndemnisationJustice\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use MonIndemnisationJustice\Entity\GeoCodePostal;
use MonIndemnisationJustice\Entity\GeoCommune;
use MonIndemnisationJustice\Entity\GeoDepartement;
use MonIndemnisationJustice\Entity\GeoPays;
use MonIndemnisationJustice\Entity\GeoRegion;

class GeoFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $this->chargerPays($manager);
        $this->chargerRegions($manager);
        $this->chargerDepartements($manager);
        $this->chargerCommunes($manager);

        $manager->flush();
    }

    protected function chargerPays(ObjectManager $manager): void
    {
        foreach ([
            'france' => (new GeoPays())
                ->setCode('FRA')
                ->setCodeInsee(GeoPays::CODE_INSEE_FRANCE)
                ->setNom('France'),
            'belgique' => (new GeoPays())
                ->setCode('BEL')
                ->setCodeInsee('99131')
                ->setNom('Belgique'),
            'maroc' => (new GeoPays())
                ->setCode('MAR')
                ->setCodeInsee('99350')
                ->setNom('Maroc'),
        ] as $reference => $pays) {
            $manager->persist($pays);
            $this->addReference("pays-$reference", $pays);
        }
    }

    protected function chargerRegions(ObjectManager $manager): void
    {
        foreach ([
            'auvergne-rhone-alpes' => (new GeoRegion())
                ->setCode('84')
                ->setNom('Auvergne-Rhône-Alpes'),
            'paca' => (new GeoRegion())
                ->setCode('93')
                ->setNom("Provence-Alpes-Côte d'Azur"),
            'idf' => (new GeoRegion())
                ->setCode('11')
                ->setNom('Île-de-France'),
            'bretagne' => (new GeoRegion())
                ->setCode('53')
                ->setNom('Bretagne'),
            'pdl' => (new GeoRegion())
                ->setCode('52')
                ->setNom('Pays de la Loire'),
        ] as $reference => $region) {
            $manager->persist($region);
            $this->addReference("region-$reference", $region);
        }
    }

    protected function chargerDepartements(ObjectManager $manager): void
    {
        foreach ([
            'isere' => (new GeoDepartement())
                ->setRegion($this->getReference('region-auvergne-rhone-alpes', GeoRegion::class))
                ->setCode('38')
                ->setNom('Isère')
                ->setDeploye(true),
            'bouches-du-rhone' => (new GeoDepartement())
                ->setRegion($this->getReference('region-paca', GeoRegion::class))
                ->setCode('13')
                ->setNom('Bouches-du-Rhône')
                ->setDeploye(true),
            'seine-et-marne' => (new GeoDepartement())
                ->setRegion($this->getReference('region-idf', GeoRegion::class))
                ->setCode('77')
                ->setNom('Seine-et-Marne')
                ->setDeploye(true),
            'ille-et-vilaine' => (new GeoDepartement())
                ->setRegion($this->getReference('region-bretagne', GeoRegion::class))
                ->setCode('35')
                ->setNom('Ille-et-Vilaine')
                ->setDeploye(true),
            'loire-atlantique' => (new GeoDepartement())
                ->setRegion($this->getReference('region-pdl', GeoRegion::class))
                ->setCode('44')
                ->setNom('Loire-Atlantique')
                ->setDeploye(false),
        ] as $reference => $departement) {
            $manager->persist($departement);
            $this->addReference("departement-{$reference}", $departement);
        }
    }

    protected function chargerCommunes(ObjectManager $manager): void
    {
        foreach ([
            'bourgoin' => (new GeoCommune())
                ->setCode('38053')
                ->setNom('Bourgoin-Jallieu')
                ->setDepartement($this->getReference('departement-isere', GeoDepartement::class)),
            'vitre' => (new GeoCommune())
                ->setCode('35360')
                ->setNom('Vitré')
                ->setDepartement($this->getReference('departement-ille-et-vilaine', GeoDepartement::class)),
        ] as $reference => $commune) {
            $manager->persist($commune);
            $this->addReference("commune-$reference", $commune);
        }

        foreach ([
            '38300' => (new GeoCodePostal())
                ->setCommune($this->getReference('commune-bourgoin', GeoCommune::class))
                ->setCodePostal('38300'),
            '35500' => (new GeoCodePostal())
                ->setCommune($this->getReference('commune-vitre', GeoCommune::class))
                ->setCodePostal('35500'),
        ] as $reference => $codePostal) {
            $manager->persist($codePostal);
            $this->addReference("code-postal-$reference", $codePostal);
        }
    }
}
