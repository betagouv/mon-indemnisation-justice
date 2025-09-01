<?php

namespace MonIndemnisationJustice\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use MonIndemnisationJustice\Entity\Adresse;
use MonIndemnisationJustice\Entity\Civilite;
use MonIndemnisationJustice\Entity\GeoCodePostal;
use MonIndemnisationJustice\Entity\GeoPays;
use MonIndemnisationJustice\Entity\PersonnePhysique;
use MonIndemnisationJustice\Entity\Requerant;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RequerantFixture extends Fixture
{
    public function __construct(
        protected readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        foreach ([
            'raquel' => (new Requerant())
                ->setAdresse(
                    (new Adresse())
                        ->setLigne1('12 rue des Oliviers')
                        ->setLocalite('Nantes')
                        ->setCodePostal('44100')
                )
                ->setPersonnePhysique(
                    (new PersonnePhysique())
                        ->setCivilite(Civilite::MME)
                        ->setPrenom1('Raquel')
                        ->setNom('Randt')
                        ->setDateNaissance(new \DateTime('1979-05-17'))
                        ->setCommuneNaissance($this->getReference('code-postal-38300', GeoCodePostal::class))
                        ->setPaysNaissance($this->getReference('pays-france', GeoPays::class))
                        ->setTelephone('0621436587')
                )
                ->setEmail('raquel.randt@courriel.fr')
                ->setVerifieCourriel()
                ->setRoles([Requerant::ROLE_REQUERANT]),
            'ray' => (new Requerant())
                ->setAdresse(
                    (new Adresse())
                        ->setLigne1('37 rue du Nivernais')
                        ->setLocalite('Rennes')
                        ->setCodePostal('35000')
                )->setPersonnePhysique(
                    (new PersonnePhysique())
                        ->setCivilite(Civilite::M)
                        ->setPrenom1('Ray')
                        ->setNom('Keran')
                        ->setDateNaissance(new \DateTime('1983-11-23'))
                        ->setCommuneNaissance($this->getReference('code-postal-35500', GeoCodePostal::class))
                        ->setPaysNaissance($this->getReference('pays-france', GeoPays::class))
                        ->setTelephone('+336 07437022')
                )
                ->setEmail('ray.keran@courriel.fr')
                ->setVerifieCourriel()
                ->setRoles([Requerant::ROLE_REQUERANT]),
        ] as $reference => $requerant) {
            $requerant->setPassword($this->passwordHasher->hashPassword($requerant, 'P4ssword'));
            $manager->persist($requerant);
            $this->addReference("requerant-$reference", $requerant);
        }

        $manager->flush();
    }
}
