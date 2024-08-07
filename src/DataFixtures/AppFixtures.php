<?php

namespace App\DataFixtures;

use App\Entity\Civilite;
use App\Entity\PersonnePhysique;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory as FakerFactory;
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
        $faker = FakerFactory::create('fr_FR');

        // Créations des civilités :
        $mademoiselle = (new Civilite())->setLibelle("Madamoiselle")->setCode('mlle');
        $madame = (new Civilite())->setLibelle("Madame")->setCode('mme');
        $monsieur = (new Civilite())->setLibelle("Monsieur")->setCode('mr');

        $civilites = [$mademoiselle, $madame, $monsieur];

        $manager->persist($mademoiselle);
        $manager->persist($madame);
        $manager->persist($monsieur);

        // Super admin

        $superAdmin = (new User())
            ->setUsername('super-admin')
            ->setEmail('super-admin@precontentieux.anje-justice.dev')
            ->addRole('ROLE_CHEF_PRECONTENTIEUX')
            ->setIsVerified(true)
            ->setActive(true)
            ->setPersonnePhysique(
                (new PersonnePhysique())
                    ->setCivilite($madame)
            );

        $superAdmin
            ->setPassword($this->hasher->hashPassword($superAdmin, 'admin'))
            ->setDateChangementMDP(new \DateTime());

        $manager->persist($superAdmin);

        // Agents
        $agent1 = (new User())
            ->setUsername('agent1')
            ->setEmail('agent1@precontentieux.anje-justice.dev')
            ->addRole('ROLE_REDACTEUR_PRECONTENTIEUX')
            ->setIsVerified(true)
            ->setActive(true)
            ->setPersonnePhysique(
                (new PersonnePhysique())
                    ->setCivilite($mademoiselle)
                    ->setPrenom1($faker->firstNameFemale)
                    ->setNom($faker->name)
            );
        $agent1
            ->setPassword($this->hasher->hashPassword($agent1, 'agent'))
            ->setDateChangementMDP(new \DateTime());
        $manager->persist($agent1);

        $agent2 = (new User())
            ->setUsername('agent2')
            ->setEmail('agent2@precontentieux.anje-justice.dev')
            ->addRole('ROLE_REDACTEUR_PRECONTENTIEUX')
            ->setIsVerified(true)
            ->setActive(true)
            ->setPersonnePhysique(
                (new PersonnePhysique())
                    ->setCivilite($mademoiselle)
                    ->setPrenom1($faker->firstNameFemale)
                    ->setNom($faker->name)
            );
        $agent2
            ->setPassword($this->hasher->hashPassword($agent2, 'agent'))
            ->setDateChangementMDP(new \DateTime());
        $manager->persist($agent2);

        $agent3 = (new User())
            ->setUsername('agent3')
            ->setEmail('agent3@precontentieux.anje-justice.dev')
            ->addRole('ROLE_REDACTEUR_PRECONTENTIEUX')
            ->setIsVerified(false)
            ->setActive(true)
            ->setPersonnePhysique(
                (new PersonnePhysique())
                    ->setCivilite($monsieur)
                ->setPrenom1($faker->firstNameMale)
                    ->setNom($faker->name)
            );
        $agent3
            ->setPassword($this->hasher->hashPassword($agent3, 'agent'))
            ->setDateChangementMDP(new \DateTime());
        $manager->persist($agent3);

        $agent4 = (new User())
            ->setUsername('agent1')
            ->setEmail('agent4@precontentieux.anje-justice.dev')
            ->addRole('ROLE_REDACTEUR_PRECONTENTIEUX')
            ->setIsVerified(true)
            ->setActive(true)
            ->setPersonnePhysique(
                (new PersonnePhysique())
                    ->setCivilite($madame)
                    ->setPrenom1($faker->firstNameFemale)
                    ->setNom($faker->name)
            );
        $agent4
            ->setPassword($this->hasher->hashPassword($agent4, 'agent'))
            ->setDateChangementMDP(new \DateTime());
        $manager->persist($agent4);

        $manager->flush();
    }
}
