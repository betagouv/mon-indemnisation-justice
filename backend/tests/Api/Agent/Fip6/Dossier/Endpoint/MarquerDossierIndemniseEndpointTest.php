<?php

namespace Api\Agent\Fip6\Dossier\Endpoint;

use MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier\MarquerDossierIndemniseEndpoint;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\AbstractEndpointTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(MarquerDossierIndemniseEndpoint::class)]
class MarquerDossierIndemniseEndpointTest extends AbstractEndpointTestCase
{
    /**
     * ETQ rédacteur du dossier, je dois pouvoir marquer le dossier comme indemnisé.
     */
    public function testMarquerDossierIndemniseRedacteurOk()
    {
        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_OK_EN_ATTENTE_PAIEMENT);

        $this->connexionAgent($dossier->getRedacteur());

        $this->client->request('POST', "/api/agent/fip6/dossier/{$dossier->getId()}/marquer-indemnise");

        $this->assertTrue($this->client->getResponse()->isOk());

        $this->em->refresh($dossier);

        $this->assertEquals(EtatDossierType::DOSSIER_OK_INDEMNISE, $dossier->getEtatDossier()->getEtat());
    }

    /**
     * ETQ agent de liaison, je dois pouvoir marquer le dossier comme indemnisé.
     */
    public function testMarquerDossierIndemniseAgentLiaisonOk()
    {
        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_OK_EN_ATTENTE_PAIEMENT);

        $this->connexion('liaison@justice.gouv.fr');

        $this->client->request('POST', "/api/agent/fip6/dossier/{$dossier->getId()}/marquer-indemnise");

        $this->assertTrue($this->client->getResponse()->isOk());

        $this->em->refresh($dossier);

        $this->assertEquals(EtatDossierType::DOSSIER_OK_INDEMNISE, $dossier->getEtatDossier()->getEtat());
    }
}
