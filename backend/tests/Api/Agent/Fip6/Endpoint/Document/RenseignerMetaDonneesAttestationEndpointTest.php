<?php

namespace MonIndemnisationJustice\Tests\Api\Agent\Fip6\Endpoint\Document;

use MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Document\RenseignerMetaDonneesAttestationEndpoint;
use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Entity\TypeAttestation;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\Endpoint\APIEndpointTestCase;

/**
 * Teste le point d'entrée @RenseignerMetaDonneesAttestationEndpoint de l'API, permettant de renseigner les métadonnées
 * d'une pièce jointe d'attestation.
 *
 * @internal
 *
 * @coversNothing
 */
class RenseignerMetaDonneesAttestationEndpointTest extends APIEndpointTestCase
{
    /**
     * ETQ agent betagouv, je dois pouvoir renseigner les méta-données de l'attestation d'un dossier qui existe.
     */
    public function testRenseignerOk(): void
    {
        $attestation = $this->em->getRepository(Document::class)->findOneBy(['type' => DocumentType::TYPE_ATTESTATION_INFORMATION]);

        $this->connexion('betagouv@justice.gouv.fr');
        $this->apiPut([
            'typeAttestation' => TypeAttestation::ANCIENNE_ATTESTATION->value,
        ], ['id' => $attestation->getId()]);

        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }

    protected function getApiRoute(): string
    {
        return '/api/agent/fip6/document/attestation/{id}/meta-donnees/renseigner';
    }
}
