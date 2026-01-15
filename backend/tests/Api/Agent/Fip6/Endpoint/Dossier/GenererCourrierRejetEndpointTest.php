<?php

namespace MonIndemnisationJustice\Tests\Api\Agent\Fip6\Endpoint\Dossier;

use MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier\GenererCourrierRejetEndpoint;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\Endpoint\APIEndpointTestCase;

/**
 * Teste le point d'entrée @GenererCourrierRejetEndpoint de l'API, permettant de générer le courrier de rejet d'un
 * dossier.
 *
 * @internal
 *
 * @coversNothing
 */
class GenererCourrierRejetEndpointTest extends APIEndpointTestCase
{
    /**
     * ETQ agent rédacteur, je dois pouvoir générer le courrier de rejet à l'état `EN_INSTRUCTION`.
     */
    public function testGenerationOk(): void
    {
        $this->connexion('redacteur@justice.gouv.fr');
        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_EN_INSTRUCTION);

        $this->genererCourrierRejet($dossier, 'est_vise');

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        /** @var \stdClass $output */
        $output = json_decode($this->client->getResponse()->getContent(), false);

        $this->assertObjectHasProperty('document', $output);
        $this->assertObjectHasProperty('type', $output->document);

        $this->assertEquals(DocumentType::TYPE_COURRIER_MINISTERE->value, $output->document->type);
    }

    protected function genererCourrierRejet(BrisPorte $dossier, string $motifRejet): void
    {
        $this->apiPost(['motifRejet' => $motifRejet], ['id' => $dossier->getId()]);
    }

    protected function getApiRoute(): string
    {
        return '/api/agent/fip6/dossier/{id}/generer-courrier-rejet';
    }
}
