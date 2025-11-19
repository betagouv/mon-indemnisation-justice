<?php

namespace MonIndemnisationJustice\Tests\Api\Agent\Fip6\Endpoint\Dossier;

use MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier\GenererDeclarationAcceptationEndpoint;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\Endpoint\AbstractEndpointTestCase;

/**
 * Teste le point d'entrée @GenererDeclarationAcceptationEndpoint de l'API, permettant de générer la déclaration
 * d'acceptation d'un dossier.
 *
 * @internal
 *
 * @coversNothing
 */
class GenererDeclarationAcceptationEndpointTest extends AbstractEndpointTestCase
{
    /**
     * ETQ agent rédacteur, je dois pouvoir générer la déclaration d'acceptation d'un dossier à l'état `EN_INSTRUCTION`.
     */
    public function testGenerationOk(): void
    {
        $this->connexion('redacteur@justice.gouv.fr');
        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_EN_INSTRUCTION);

        $this->genererDeclarationAcceptation($dossier, 1234.56);

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        /** @var \stdClass $output */
        $output = json_decode($this->client->getResponse()->getContent(), false);

        $this->assertObjectHasProperty('document', $output);
        $this->assertObjectHasProperty('type', $output->document);

        $this->assertEquals(DocumentType::TYPE_COURRIER_REQUERANT->value, $output->document->type);
    }

    protected function genererDeclarationAcceptation(BrisPorte $dossier, float $montantIndemnisation): void
    {
        $this->apiPost(['montantIndemnisation' => $montantIndemnisation], ['id' => $dossier->getId()]);
    }

    protected function getApiRoute(): string
    {
        return '/api/agent/fip6/dossier/{id}/generer-declaration-acceptation';
    }
}
