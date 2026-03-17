<?php

namespace MonIndemnisationJustice\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use MonIndemnisationJustice\Entity\Civilite;
use MonIndemnisationJustice\Entity\Personne;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PersonneFixture extends Fixture
{
    protected Generator $faker;

    public function __construct(
        protected readonly UserPasswordHasherInterface $passwordHasher,
        #[Autowire(param: 'kernel.environment')]
        public readonly string $environment,
    ) {
        $this->faker = Factory::create('fr_FR');
    }

    public function load(ObjectManager $manager): void
    {
        foreach ($this->personnes() as $reference => $personne) {
            $manager->persist($personne);
            $this->addReference("personne-$reference", $personne);
        }

        $manager->flush();
    }

    /**
     * @return Personne[]
     */
    private function personnes(): array
    {
        return [
            'raquel' => new Personne()
                ->setCivilite(Civilite::MME)
                ->setPrenom('Raquel')
                ->setNom('Randt')
                ->setTelephone('0621436587')
                ->setCourriel(!in_array($this->environment, ['test', 'ci']) ? 'wossewodda-3728@yopmail.com' : 'raquel.randt@courriel.fr'),
            'ray' => new Personne()
                ->setCivilite(Civilite::M)
                ->setPrenom('Ray')
                ->setNom('Keran')
                ->setCourriel('ray.keran@courriel.fr')
                ->setTelephone('+336 07437022'),
            'melun' => new Personne()
                ->setCivilite(Civilite::MME)
                ->setPrenom($this->faker->firstNameFemale())
                ->setNom($this->faker->lastName())
                ->setTelephone($this->faker->phoneNumber())
                ->setCourriel($this->faker->email()),
            'aix-en-provence' => new Personne()
                ->setCivilite(Civilite::M)
                ->setPrenom($this->faker->firstNameMale())
                ->setNom($this->faker->lastName())
                ->setTelephone($this->faker->phoneNumber())
                ->setCourriel($this->faker->email()),
            'saint-malo' => new Personne()
                ->setCivilite(Civilite::MME)
                ->setPrenom($this->faker->firstNameFemale())
                ->setNom($this->faker->lastName())
                ->setTelephone($this->faker->phoneNumber())
                ->setCourriel($this->faker->email()),
            'ancenis' => new Personne()
                ->setCivilite(Civilite::M)
                ->setPrenom($this->faker->firstNameMale())
                ->setNom($this->faker->lastName())
                ->setTelephone($this->faker->phoneNumber())
                ->setCourriel($this->faker->email()),
            'istres' => new Personne()
                ->setCivilite(Civilite::M)
                ->setPrenom($this->faker->firstNameMale())
                ->setNom($this->faker->lastName())
                ->setTelephone($this->faker->phoneNumber())
                ->setCourriel($this->faker->email()),

        ];
    }
}
