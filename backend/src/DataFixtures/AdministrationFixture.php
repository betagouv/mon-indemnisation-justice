<?php

namespace MonIndemnisationJustice\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use MonIndemnisationJustice\Entity\Administration;
use MonIndemnisationJustice\Entity\AdministrationType;

class AdministrationFixture extends Fixture implements FixtureGroupInterface
{
    public static function getGroups(): array
    {
        return ['develop'];
    }

    public function load(ObjectManager $manager): void
    {
        foreach ([
            'MJ' => new Administration()
                ->setType(AdministrationType::MINISTERE_JUSTICE)
                ->setSiret(AdministrationType::SIRET_MJ)
                ->setDateIntegration(\DateTimeImmutable::createFromFormat('Y-m-d', '2024-06-30')),
            'PN' => new Administration()
                ->setType(AdministrationType::POLICE_NATIONALE)
                ->setSiret(AdministrationType::SIRET_PN)
                ->setDateIntegration(\DateTimeImmutable::createFromFormat('Y-m-d', '2024-11-07')),
            'GN' => new Administration()
                ->setType(AdministrationType::GENDARMERIE_NATIONALE)
                ->setSiret(AdministrationType::SIRET_GN)
                ->setDateIntegration(\DateTimeImmutable::createFromFormat('Y-m-d', '2025-03-20')),
            'PP' => new Administration()
                ->setType(AdministrationType::PREFECTURE_DE_POLICE)
                ->setSiret(AdministrationType::SIRET_PP),
            'MI' => new Administration()
                ->setType(AdministrationType::MINISTERE_INTERIEUR)
                ->setSiret(AdministrationType::SIRET_MI),
        ] as $reference => $administration) {
            $manager->persist($administration);
            $this->addReference("administration-$reference", $administration);
        }

        $manager->flush();
    }

    protected function getCacheKey(): string
    {
        return 'fixture-agent';
    }
}
