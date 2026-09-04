<?php

namespace MonIndemnisationJustice\Tests\Api\Agent\Fip6\Dossier\Endpoint;

use MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier\AnnoterDossierEndpoint;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\AbstractEndpointTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Teste le point d'entrée @AnnoterDossierEndpoint de l'API, permettant d'annoter un dossier.
 */
#[CoversClass(AnnoterDossierEndpoint::class)]
class AnnoterDossierEndpointTest extends AbstractEndpointTestCase
{
    /**
     * ETQ rédacteur, je dois pouvoir annoter un dossier.
     */
    public function testAnnoterOk(): void
    {
        $redacteur = $this->connexion('redacteur@justice.gouv.fr');
        // $dossier = $this->getDossierParReference('dossier-a-attribuer-melun');
        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_A_INSTRUIRE);

        $this->client->request('POST', "/api/agent/fip6/dossier/{$dossier->getId()}/annoter", [
            'notes' => 'Ce dossier contient désormais une note',
        ]);

        $this->assertTrue($this->client->getResponse()->isOk());

        $this->em->refresh($dossier);

        $this->assertEquals('Ce dossier contient désormais une note', $dossier->getNotes());
    }
}
