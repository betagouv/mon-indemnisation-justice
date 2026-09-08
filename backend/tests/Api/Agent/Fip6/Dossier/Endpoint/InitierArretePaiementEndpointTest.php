<?php

namespace MonIndemnisationJustice\Tests\Api\Agent\Fip6\Dossier\Endpoint;

use MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier\InitierArretePaiementEndpoint;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Service\DocumentManager;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\APIEndpointTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;

/**
 * Teste le point d'entrée @InitierArretePaiementEndpoint de l'API, permettant au rédacteur de vérifier l'arrêté de
 * paiement d'un dossier et de le faire avancer à l'état `OK_VERIFIE`.
 */
#[CoversClass(InitierArretePaiementEndpoint::class)]
class InitierArretePaiementEndpointTest extends APIEndpointTestCase
{
    /**
     * ETQ agent rédacteur attribué, je dois pouvoir initier l'arrêté de paiement d'un dossier dont l'arrêté a été
     * généré.
     */
    public function testInitiationOk(): void
    {
        $this->connexion('redacteur@justice.gouv.fr');
        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_OK_A_VERIFIER);
        $this->genererArretePaiementDocument($dossier);

        $this->initierArretePaiement($dossier);

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        /** @var \stdClass $output */
        $output = json_decode($this->client->getResponse()->getContent(), false);

        $this->assertObjectHasProperty('etat', $output);
        $this->assertObjectHasProperty('etat', $output->etat);
        $this->assertEquals(EtatDossierType::DOSSIER_OK_VERIFIE->value, $output->etat->etat);
    }

    /**
     * ETQ agent rédacteur, je ne dois pas pouvoir initier l'arrêté de paiement d'un dossier dont l'arrêté n'a pas
     * encore été généré.
     */
    public function testInitiationKoArreteNonGenere(): void
    {
        $this->connexion('redacteur@justice.gouv.fr');
        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_OK_A_VERIFIER);

        $this->initierArretePaiement($dossier);

        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    protected function genererArretePaiementDocument(Dossier $dossier): void
    {
        /** @var DocumentManager $documentManager */
        $documentManager = self::getContainer()->get(DocumentManager::class);
        $documentManager->generer($dossier, DocumentType::TYPE_ARRETE_PAIEMENT);
    }

    protected function initierArretePaiement(Dossier $dossier): void
    {
        $this->apiPost([], ['id' => $dossier->getId()]);
    }

    protected function getApiRoute(): string
    {
        return '/api/agent/fip6/dossier/{id}/initier-arrete-paiement';
    }
}
