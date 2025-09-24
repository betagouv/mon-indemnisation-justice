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
                ->setUid('1234')
                ->setValide(),
            'validateur' => (new Agent())
                ->setIdentifiant('dda38b83-bca4-4a25-8e2a-d2eb4947f02d')
                ->setEmail('validateur@justice.gouv.fr')
                ->setPrenom('Walid')
                ->setRoles([Agent::ROLE_AGENT, Agent::ROLE_AGENT_VALIDATEUR, Agent::ROLE_AGENT_DOSSIER])
                ->setNom('Hateur')
                ->setUid('551')->setValide(),
            'attributeur' => (new Agent())
                ->setIdentifiant('14ea0686-9179-447a-a0b0-cdff4419befc')
                ->setEmail('attributeur@justice.gouv.fr')
                ->setPrenom('Hat')
                ->setRoles([Agent::ROLE_AGENT, Agent::ROLE_AGENT_ATTRIBUTEUR, Agent::ROLE_AGENT_DOSSIER])
                ->setNom('Tributeur')
                ->setUid('7301')
                ->setValide(),
            'liaison' => (new Agent())
                ->setIdentifiant('90d7335e-be32-4780-9bb3-6078f4482ece')
                ->setEmail('liaison@justice.gouv.fr')
                ->setPrenom('Lison')
                ->setRoles([Agent::ROLE_AGENT, Agent::ROLE_AGENT_LIAISON_BUDGET])
                ->setNom('Bude-Jay')
                ->setUid('119')
                ->setValide(),
            'betagouv' => (new Agent())
                ->setIdentifiant('fd38a059-9f32-4b59-8f01-96d3284bf1cc')
                ->setEmail('betagouv@justice.gouv.fr')
                ->setPrenom('Betta')
                ->setRoles([Agent::ROLE_AGENT, Agent::ROLE_AGENT_BETAGOUV, Agent::ROLE_AGENT_DOSSIER])
                ->setNom('Gouve')
                ->setUid('1337')
                ->setValide(),
        ] as $reference => $agent) {
            $manager->persist($agent);
            $this->addReference("agent-{$reference}", $agent);
        }

        $manager->flush();
    }

    protected function getCacheKey(): string
    {
        return 'fixture-agent';
    }
}
