<?php

namespace MonIndemnisationJustice\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use MonIndemnisationJustice\Entity\Adresse;
use MonIndemnisationJustice\Entity\Civilite;
use MonIndemnisationJustice\Entity\GeoCodePostal;
use MonIndemnisationJustice\Entity\GeoCommune;
use MonIndemnisationJustice\Entity\GeoDepartement;
use MonIndemnisationJustice\Entity\GeoPays;
use MonIndemnisationJustice\Entity\PersonnePhysique;
use MonIndemnisationJustice\Entity\Usager;

class DevelopFixture extends Fixture implements FixtureGroupInterface, DependentFixtureInterface
{
    public static function getGroups(): array
    {
        return ['develop'];
    }

    public function getDependencies(): array
    {
        return [AgentFixture::class, GeoFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        foreach ([
            'raquel' => new Usager()
                ->setSub('c1722a03-4172-4015-9f0d-d1995d4cbe5c')
                ->setAdresse(
                    new Adresse()
                        ->setLigne1('20 avenue de Ségur')
                        ->setLocalite('Paris')
                        ->setCodePostal('75007')
                )
                ->setPersonnePhysique(
                    new PersonnePhysique()
                        ->setCivilite(Civilite::MME)
                        ->setPrenom1('Angela')
                        ->setPrenom2('Claire')
                        ->setPrenom3('Louis')
                        ->setNom('DUBOIS')
                        ->setDateNaissance(new \DateTime('1962-08-24'))
                        ->setCommuneNaissance(
                            new GeoCodePostal()
                                ->setCodePostal('75007')
                                ->setCommune(
                                    new GeoCommune()
                                        ->setCode('75107')
                                        ->setDepartement($this->getReference('departement-paris', GeoDepartement::class))
                                        ->setNom('Paris')
                                )
                        )
                        ->setPaysNaissance($this->getReference('pays-france', GeoPays::class))
                        ->setTelephone('0123456789')
                )
                ->setEmail('wossewodda-3728@yopmail.com')
                ->setVerifieCourriel()
                ->setRoles([Usager::ROLE_REQUERANT]),
        ] as $reference => $requerant) {

            $manager->persist($requerant);
            $this->addReference("requerant-{$reference}", $requerant);
        }

        $manager->flush();
    }
}
