<?php

namespace MonIndemnisationJustice\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use MonIndemnisationJustice\Entity\Agent;

class AgentFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        foreach ([
            'redacteur' => (new Agent())
                ->setIdentifiant('c1722a03-4172-4015-9f0d-d1995d4cbe5c')
                ->setEmail('redacteur@justice.gouv.fr')
                ->setPrenom('Red')
                ->setRoles([Agent::ROLE_AGENT, Agent::ROLE_AGENT_REDACTEUR, Agent::ROLE_AGENT_DOSSIER])
                ->setNom('Acteur')
                ->setUid('1234'),
            'validateur' => (new Agent())
                ->setIdentifiant('dda38b83-bca4-4a25-8e2a-d2eb4947f02d')
                ->setEmail('validateur@justice.gouv.fr')
                ->setPrenom('Walid')
                ->setRoles([Agent::ROLE_AGENT, Agent::ROLE_AGENT_VALIDATEUR, Agent::ROLE_AGENT_DOSSIER])
                ->setNom('Hateur')
                ->setUid('551'),
            'attributeur' => (new Agent())
                ->setIdentifiant('14ea0686-9179-447a-a0b0-cdff4419befc')
                ->setEmail('attributeur@justice.gouv.fr')
                ->setPrenom('Hat')
                ->setRoles([Agent::ROLE_AGENT, Agent::ROLE_AGENT_ATTRIBUTEUR])
                ->setNom('Tributeur')
                ->setUid('7301'),
        ] as $reference => $agent) {
            $manager->persist($agent);
            $this->addReference("agent-$reference", $agent);
        }

        $manager->flush();
    }
}
