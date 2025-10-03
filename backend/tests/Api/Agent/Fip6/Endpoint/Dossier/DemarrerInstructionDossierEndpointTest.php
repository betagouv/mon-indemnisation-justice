<?php

namespace Api\Agent\Fip6\Endpoint\Dossier;

use MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier\DemarrerInstructionDossierEndpoint;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\Endpoint\AbstractEndpointTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Teste le point d'entrée @DemarrerInstructionDossierEndpoint de l'API, permettant au rédacteur attribué de démarrer
 * l'instruction.
 *
 * @internal
 *
 * @coversNothing
 */
class DemarrerInstructionDossierEndpointTest extends AbstractEndpointTestCase
{
    /**
     * ETQ rédacteur attribué, je dois pouvoir démarrer l'instruction d'un dossier à instruire.
     */
    public function testDemarrerInstructionOk(): void
    {
        $redacteur = $this->connexion('redacteur@justice.gouv.fr');
        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_A_INSTRUIRE);

        $this->demarrerInstruction($dossier);

        $this->assertTrue($this->client->getResponse()->isOk());

        /** @var \stdClass $output */
        $output = json_decode($this->client->getResponse()->getContent(), false);

        $this->assertObjectHasProperty('etat', $output);
        $this->assertObjectHasProperty('etat', $output->etat);

        $this->assertEquals(EtatDossierType::DOSSIER_EN_INSTRUCTION->value, $output->etat->etat);
        $this->assertObjectHasProperty('agent', $output->etat);
        $this->assertEquals($redacteur->getId(), $output->etat->agent);
    }

    /**
     * ETQ rédacteur pas attribué, je ne dois pas pouvoir démarrer l'instruction d'un dossier à instruire.
     */
    public function testDemarrerInstructionKoRedacteurPasAttribue(): void
    {
        $this->connexion('reda.k-theur@justice.gouv.fr');
        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_A_INSTRUIRE);

        $this->demarrerInstruction($dossier);

        $this->assertFalse($this->client->getResponse()->isOk());
        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());

        /** @var \stdClass $output */
        $output = json_decode($this->client->getResponse()->getContent(), false);

        $this->assertObjectHasProperty('erreur', $output);
        $this->assertEquals("Seul l'agent rédacteur attribué peut instruire un dossier", $output->erreur);
    }

    protected function demarrerInstruction(BrisPorte $dossier): void
    {
        $this->apiPost([], ['id' => $dossier->getId()]);
    }

    protected function getApiRoute(): string
    {
        return '/api/agent/fip6/dossier/{id}/demarrer-instruction';
    }
}
