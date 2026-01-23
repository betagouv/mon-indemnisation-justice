<?php

namespace MonIndemnisationJustice\Tests\Api\Agent\Fip6\Dossier\Endpoint;

use MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier\GenererArretePaiementEndpoint;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\APIEndpointTestCase;

/**
 * Teste le point d'entrée @GenererArretePaiementEndpoint de l'API, permettant de générer l'arrêté de paiement d'un
 * dossier.
 *
 * @internal
 *
 * @coversNothing
 */
class GenererArretePaiementEndpointTest extends APIEndpointTestCase
{
    /**
     * ETQ agent rédacteur, je dois pouvoir générer l'arrêté d'un dossier à l'état `OK_A_VERIFIER`.
     */
    public function testGenerationOk(): void
    {
        $this->connexion('redacteur@justice.gouv.fr');
        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_OK_A_VERIFIER);

        $this->genererArretePaiement($dossier);

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        /** @var \stdClass $output */
        $output = json_decode($this->client->getResponse()->getContent(), false);

        $this->assertObjectHasProperty('document', $output);
        $this->assertObjectHasProperty('type', $output->document);

        $this->assertEquals(DocumentType::TYPE_ARRETE_PAIEMENT->value, $output->document->type);
    }

    protected function genererArretePaiement(BrisPorte $dossier): void
    {
        $this->apiPost([], ['id' => $dossier->getId()]);
    }

    protected function getApiRoute(): string
    {
        return '/api/agent/fip6/dossier/{id}/generer-arrete-paiement';
    }
}
