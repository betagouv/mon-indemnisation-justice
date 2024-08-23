<?php

namespace App\DataFixtures;

use App\Entity\Categorie;
use App\Entity\Civilite;
use App\Entity\PersonnePhysique;
use App\Entity\QualiteRequerant;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    public function __construct(
        protected readonly UserPasswordHasherInterface $hasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Provisionnement des qualités de requérant:
        $donneesQualiteRequrant = [
            ['code' => '1', 'mnemo' => 'PRO', 'libelle' => 'Propriétaire'],
            ['code' => '2', 'mnemo' => 'LOC', 'libelle' => 'Locataire'],
            ['code' => '3', 'mnemo' => 'HEB', 'libelle' => 'Hébergeant'],
            ['code' => '4', 'mnemo' => 'AUT', 'libelle' => 'Autre'],
        ];

        $qualiteRequerants = [];

        foreach ($donneesQualiteRequrant as $donneeQualiteRequrant) {
            $qualiteRequerants[$donneeQualiteRequrant['mnemo']] = (new QualiteRequerant())
                    ->setCode($donneeQualiteRequrant['code'])
                    ->setMnemo($donneeQualiteRequrant['mnemo'])
                    ->setLibelle($donneeQualiteRequrant['libelle']);
            $manager->persist($qualiteRequerants[$donneeQualiteRequrant['mnemo']]);
        }

        // Provisionnement des civilités:
        $donneesCivilite = [
            ['code' => '1', 'mnemo' => 'M', 'libelle' => 'Monsieur'],
            ['code' => '2', 'mnemo' => 'MME', 'libelle' => 'Madame'],
        ];

        $civilites = [];

        foreach ($donneesCivilite as $donneeCivilite) {
            $civilites[$donneeCivilite['mnemo']] = (new Civilite())
                    ->setCode($donneeCivilite['code'])
                    ->setMnemo($donneeCivilite['mnemo'])
                    ->setLibelle($donneeCivilite['libelle']);
            $manager->persist($civilites[$donneeCivilite['mnemo']]);
        }

        // Provisionnement des catégories:
        $donneesCategorie = [
            ['code' => '1', 'mnemo' => 'BRI', 'libelle' => 'Bris de porte'],
        ];

        $categories = [];

        foreach ($donneesCategorie as $donneeCategorie) {
            $categories[$donneeCategorie['mnemo']] = (new Categorie())
                    ->setCode($donneeCategorie['code'])
                    ->setMnemo($donneeCategorie['mnemo'])
                    ->setLibelle($donneeCategorie['libelle']);
            $manager->persist($categories[$donneeCategorie['mnemo']]);
        }

        // Provisionnement des agents :
        $donneesAgent = [
            ['nom' => 'Le Texier', 'prenom' => 'Alexandra', 'email' => 'alexandra.le-texier@justice.gouv.fr', 'civilite' => 'MME', 'admin' => false],
        ];

        foreach ($donneesAgent as $donneeAgent) {
            $agent = (new User())
            ->setUsername(self::slugify(sprintf('%s.%s', $donneeAgent['prenom'], $donneeAgent['nom'])))
            ->setEmail($donneeAgent['email'])
            ->addRole($donneeAgent['admin'] ? User::ROLE_CHEF_PRECONTENTIEUX : User::ROLE_REDACTEUR_PRECONTENTIEUX)
            ->setIsVerified(true)
            ->setActive(true)
            ->setPersonnePhysique(
                (new PersonnePhysique())
                    ->setCivilite($civilites[$donneeAgent['civilite']])
                    ->setPrenom1($donneeAgent['prenom'])
                    ->setNom($donneeAgent['nom'])
            );
            $agent->setPassword($this->hasher->hashPassword($agent, 'P4ssword'));

            $manager->persist($agent);
        }

        $manager->flush();
    }

    protected static function slugify(string $text): string
    {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $text)));
    }
}
