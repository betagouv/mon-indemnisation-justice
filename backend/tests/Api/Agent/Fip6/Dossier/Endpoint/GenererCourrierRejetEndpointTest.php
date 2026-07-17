<?php

namespace MonIndemnisationJustice\Tests\Api\Agent\Fip6\Dossier\Endpoint;

use MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier\GenererCourrierRejetEndpoint;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Entity\MotifRejetBrisPorte;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\APIEndpointTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Teste le point d'entrée @GenererCourrierRejetEndpoint de l'API, permettant de générer le courrier de rejet d'un
 * dossier.
 */
#[CoversClass(GenererCourrierRejetEndpoint::class)]
class GenererCourrierRejetEndpointTest extends APIEndpointTestCase
{
    /**
     * ETQ agent rédacteur, je dois pouvoir générer le courrier de rejet à l'état `EN_INSTRUCTION`.
     */
    public function testGenerationOk(): void
    {
        $this->connexion('redacteur@justice.gouv.fr');
        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_EN_INSTRUCTION);

        $this->genererCourrierRejet($dossier, MotifRejetBrisPorte::MIS_EN_CAUSE);

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        /** @var \stdClass $output */
        $output = json_decode($this->client->getResponse()->getContent(), false);

        $this->assertObjectHasProperty('document', $output);
        $this->assertObjectHasProperty('type', $output->document);

        $this->assertEquals(DocumentType::TYPE_COURRIER_MINISTERE->value, $output->document->type);
    }

    protected function genererCourrierRejet(Dossier $dossier, MotifRejetBrisPorte $motifRejet): void
    {
        $this->apiPost(['motifRejet' => $motifRejet->value], ['id' => $dossier->getId()]);
    }

    protected function getApiRoute(): string
    {
        return '/api/agent/fip6/dossier/{id}/generer-courrier-rejet';
    }
}
