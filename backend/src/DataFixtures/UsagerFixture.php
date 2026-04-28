<?php

namespace MonIndemnisationJustice\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use MonIndemnisationJustice\Entity\Personne;
use MonIndemnisationJustice\Entity\Usager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UsagerFixture extends Fixture implements DependentFixtureInterface
{
    protected Generator $faker;

    public function __construct(
        protected readonly UserPasswordHasherInterface $passwordHasher,
        #[Autowire(param: 'kernel.environment')]
        public readonly string $environment,
    ) {
        $this->faker = Factory::create('fr_FR');
    }

    public function getDependencies(): array
    {
        return [
            PersonneFixture::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        foreach ([
            'raquel' => new Usager()
                ->setPersonne(
                    $this->getReference('personne-raquel', Personne::class)
                )
                ->setEmail(!in_array($this->environment, ['test', 'ci']) ? 'wossewodda-3728@yopmail.com' : 'raquel.randt@courriel.fr')
                ->setVerifieCourriel()
                ->setRoles([Usager::ROLE_REQUERANT]),
            'ray' => new Usager()
                ->setPersonne(
                    $this->getReference('personne-ray', Personne::class)
                )
                ->setEmail('ray.keran@courriel.fr')
                ->setSub('3297f962-d6a2-4e30-a134-4b85615fd62c')
                ->setVerifieCourriel()
                ->setRoles([Usager::ROLE_REQUERANT]),
            'melun' => new Usager()
                ->setPersonne(
                    $this->getReference('personne-melun', Personne::class)
                )
                ->setEmail($this->faker->email())
                ->setVerifieCourriel()
                ->setRoles([Usager::ROLE_REQUERANT]),
            'aix-en-provence' => new Usager()
                ->setPersonne(
                    $this->getReference('personne-aix-en-provence', Personne::class)
                )
                ->setEmail($this->faker->email())
                ->setVerifieCourriel()
                ->setRoles([Usager::ROLE_REQUERANT]),
            'saint-malo' => new Usager()
                ->setPersonne(
                    $this->getReference('personne-saint-malo', Personne::class)
                )
                ->setEmail($this->faker->email())
                ->setVerifieCourriel()
                ->setRoles([Usager::ROLE_REQUERANT]),
            'ancenis' => new Usager()
                ->setPersonne(
                    $this->getReference('personne-ancenis', Personne::class)
                )
                ->setEmail($this->faker->email())
                ->setVerifieCourriel()
                ->setRoles([Usager::ROLE_REQUERANT]),
            'istres' => new Usager()
                ->setPersonne(
                    $this->getReference('personne-istres', Personne::class)
                )
                ->setEmail($this->faker->email())
                ->setVerifieCourriel()
                ->setRoles([Usager::ROLE_REQUERANT]),
        ] as $reference => $requerant) {
            /** @var Usager $requerant */
            if (null === $requerant->getSub()) {
                $requerant->setPassword($this->passwordHasher->hashPassword($requerant, 'P4ssword'));
            }

            $manager->persist($requerant);
            $this->addReference("requerant-{$reference}", $requerant);
        }

        $manager->flush();
    }

    protected function getCacheKey(): string
    {
        return 'fixture-requerant';
    }
}
