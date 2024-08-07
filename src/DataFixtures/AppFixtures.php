<?php

namespace App\DataFixtures;

use App\Entity\Civilite;
use App\Entity\PersonnePhysique;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        protected readonly UserPasswordHasherInterface $hasher
    )
    {
    }


    public function load(ObjectManager $manager): void
    {
        // Créations des civilités :
        $mademoiselle = (new Civilite())->setLibelle("Madamoiselle")->setCode('mlle');
        $madame = (new Civilite())->setLibelle("Madame")->setCode('mme');
        $monsieur = (new Civilite())->setLibelle("Monsieur")->setCode('mr');

        $manager->persist($mademoiselle);
        $manager->persist($madame);
        $manager->persist($monsieur);

        $admin = (new User())
            ->setUsername('super-admin')
            ->setEmail('super-admin@precontentieux.anje-justice.dev')
            ->addRole('ROLE_ADMIN_FONC')
            ->setIsVerified(true)
            ->setActive(true)
            ->setPersonnePhysique(
                (new PersonnePhysique())
                    ->setCivilite($madame)
            );

        $admin
            ->setPassword($this->hasher->hashPassword($admin, 'admin'))
            ->setDateChangementMDP(new \DateTime());

        $manager->persist($admin);

        $manager->flush();
    }
}
