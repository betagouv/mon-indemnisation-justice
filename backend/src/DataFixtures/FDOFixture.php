<?php

namespace MonIndemnisationJustice\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\BrouillonDeclarationFDOBrisPorte;

class FDOFixture extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            AgentFixture::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $brouillon = (new BrouillonDeclarationFDOBrisPorte())
            ->setDateCreation(new \DateTime())
            ->setAgent($this->getReference('agent-policier', Agent::class))
            ->setDonnees([
                'estErreur' => 'DOUTE',
                'descriptionErreur' => 'Cassé la porte',
                'dateOperation' => (new \DateTime())->sub(\DateInterval::createFromDateString('3 days'))->format('Y-m-d'),
            ])
        ;
        $manager->persist($brouillon);
        $manager->flush();
    }

    protected function getCacheKey(): string
    {
        return 'fixture-agent';
    }
}
