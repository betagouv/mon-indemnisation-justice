<?php

namespace MonIndemnisationJustice\Tests\Api\Agent\Fip6\Dossier\Endpoint;

use MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier\TransmettreAFIP3Endpoint;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\AbstractEndpointTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TransmettreAFIP3Endpoint::class)]
class TransmettreAFIP3EndpointTest extends AbstractEndpointTestCase
{
    /**
     * ETQ rédacteur du dossier, je dois pouvoir transmettre le dossier à FIP3.
     */
    public function testTransmettreAFIP3RedacteurOk()
    {
        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_OK_A_INDEMNISER);

        $this->connexionAgent($dossier->getRedacteur());

        $this->client->request('POST', "/api/agent/fip6/dossier/{$dossier->getId()}/transmettre-a-fip3");

        $this->assertTrue($this->client->getResponse()->isOk());

        $this->em->refresh($dossier);

        $this->assertEquals(EtatDossierType::DOSSIER_OK_EN_ATTENTE_PAIEMENT, $dossier->getEtatDossier()->getEtat());
    }

    /**
     * ETQ agent de liaison, je dois pouvoir transmettre le dossier à FIP3.
     */
    public function testTransmettreAFIP3AgentLiaisonOk()
    {
        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_OK_A_INDEMNISER);

        $this->connexion('liaison@justice.gouv.fr');

        $this->client->request('POST', "/api/agent/fip6/dossier/{$dossier->getId()}/transmettre-a-fip3");

        $this->assertTrue($this->client->getResponse()->isOk());

        $this->em->refresh($dossier);

        $this->assertEquals(EtatDossierType::DOSSIER_OK_EN_ATTENTE_PAIEMENT, $dossier->getEtatDossier()->getEtat());
    }
}
