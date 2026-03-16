<?php

namespace MonIndemnisationJustice\Tests\Api\Agent\Fip6\Agent\Endpoint;

use MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Agent\DeclarerNouvelAgentEndpoint;
use MonIndemnisationJustice\Entity\Administration;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\AbstractEndpointTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DeclarerNouvelAgentEndpoint::class)]
class DeclarerNouvelAgentEndpointTest extends AbstractEndpointTestCase
{
    /**
     * ETQ agent gestionnaire, je peux créer un nouvel agent dont le courriel n'est pas encore associé à un agent
     * existant.
     */
    public function testDeclarationOk(): void
    {
        $this->connexion('gestion@justice.gouv.fr');

        $this->client->request('PUT', '/api/agent/fip6/agents/creer', [
            'prenom' => 'Pau',
            'nom' => 'Lee-Sillez',
            'courriel' => 'pau.lee-sillez@interieur.gouv.fr',
            'administration' => Administration::POLICE_NATIONALE->value,
            'roles' => [Agent::ROLE_AGENT_FORCES_DE_L_ORDRE],
        ]);

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        /** @var object $output */
        $output = json_decode($this->client->getResponse()->getContent());

        $this->assertObjectHasProperty('roles', $output);
        $this->assertIsArray($output->roles);
        $this->assertContains(Agent::ROLE_AGENT_FORCES_DE_L_ORDRE, $output->roles);
        $this->assertContains(Agent::ROLE_AGENT, $output->roles);
    }
}
