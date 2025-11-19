<?php

namespace MonIndemnisationJustice\Tests\Api\Agent\Fip6\Endpoint\Dossier;

use MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier\GenererCourrierRejetEndpoint;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\Endpoint\AbstractEndpointTestCase;

/**
 * Teste le point d'entrée @GenererCourrierRejetEndpoint de l'API, permettant de générer le courrier de proposition
 * d'indemnisation d'un dossier.
 *
 * @internal
 *
 * @coversNothing
 */
class GenererCourrierPropositionIndemnisationEndpointTest extends AbstractEndpointTestCase
{
    /**
     * ETQ agent rédacteur, je dois pouvoir générer le courrier de proposition d'indemnisation à l'état `EN_INSTRUCTION`.
     */
    public function testGenerationOk(): void
    {
        $this->connexion('redacteur@justice.gouv.fr');
        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_EN_INSTRUCTION);

        $this->genererCourrierRejet($dossier, 1234.56);

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        /** @var \stdClass $output */
        $output = json_decode($this->client->getResponse()->getContent(), false);

        $this->assertObjectHasProperty('document', $output);
        $this->assertObjectHasProperty('type', $output->document);

        $this->assertEquals(DocumentType::TYPE_COURRIER_MINISTERE->value, $output->document->type);
    }

    protected function genererCourrierRejet(BrisPorte $dossier, float $montantIndemnisation): void
    {
        $this->apiPost(['montantIndemnisation' => $montantIndemnisation], ['id' => $dossier->getId()]);
    }

    protected function getApiRoute(): string
    {
        return '/api/agent/fip6/dossier/{id}/generer-courrier-rejet';
    }
}
