<?php

namespace MonIndemnisationJustice\Tests\Api\Agent\Fip6\Dossier\Endpoint;

use MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier\RevenirArreteDossierEndpoint;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\AbstractEndpointTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;

/**
 * Teste le point d'entrée @RevenirArreteDossierEndpoint de l'API, permettant au rédacteur attribué de faire revenir
 * un dossier à l'étape de vérification de la déclaration d'acceptation et de génération de l'arrêté de paiement.
 */
#[CoversClass(RevenirArreteDossierEndpoint::class)]
class RevenirArreteDossierEndpointTest extends AbstractEndpointTestCase
{
    /**
     * ETQ rédacteur attribué, je dois pouvoir faire revenir à l'arrêté un dossier vérifié ou en attente
     * d'indemnisation.
     */
    #[DataProvider('dossierEnAttenteRevenirArreteProvider')]
    public function testRevenirArreteOk(EtatDossierType $etat, string $emailRedacteur): void
    {
        $redacteur = $this->connexion($emailRedacteur);
        $dossier = $this->getDossierParEtat($etat);

        $this->client->request('POST', "/api/agent/fip6/dossier/{$dossier->getId()}/revenir-arrete");

        $this->assertTrue($this->client->getResponse()->isOk());

        /** @var \stdClass $output */
        $output = json_decode($this->client->getResponse()->getContent(), false);

        $this->assertObjectHasProperty('etat', $output);
        $this->assertObjectHasProperty('etat', $output->etat);

        $this->assertEquals(EtatDossierType::DOSSIER_OK_A_VERIFIER->value, $output->etat->etat);
        $this->assertObjectHasProperty('redacteur', $output->etat);
        $this->assertEquals($redacteur->getId(), $output->etat->redacteur->id);

        $this->em->refresh($dossier);

        $this->assertEquals(EtatDossierType::DOSSIER_OK_A_VERIFIER, $dossier->getEtatDossier()->getEtat());
    }

    /**
     * @return array<string, array{0: EtatDossierType, 1: string}>
     */
    public static function dossierEnAttenteRevenirArreteProvider(): array
    {
        return [
            'ok_verifie' => [EtatDossierType::DOSSIER_OK_VERIFIE, 'redacteur@justice.gouv.fr'],
            'ok_a_indemniser' => [EtatDossierType::DOSSIER_OK_A_INDEMNISER, 'redacteur@justice.gouv.fr'],
        ];
    }

    /**
     * ETQ agent non habilité (rédacteur non attribué ou agent validateur), je ne dois pas pouvoir faire revenir à
     * l'arrêté un dossier vérifié, cette action étant réservée au rédacteur attribué.
     */
    #[DataProvider('agentNonHabiliteRevenirArreteProvider')]
    public function testRevenirArreteKoPasHabilite(string $emailAgent, EtatDossierType $etat): void
    {
        $dossier = $this->getDossierParEtat($etat);
        $agent = $this->connexion($emailAgent);
        $this->assertNotEquals($agent->getId(), $dossier->getRedacteur()->getId());

        $this->client->request('POST', "/api/agent/fip6/dossier/{$dossier->getId()}/revenir-arrete");

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());

        /** @var \stdClass $output */
        $output = json_decode($this->client->getResponse()->getContent(), false);

        $this->assertObjectHasProperty('erreur', $output);
        $this->assertEquals("Seul le rédacteur attribué peut faire revenir ce dossier à l'édition de l'arrêté de paiement", $output->erreur);
    }

    /**
     * @return array<string, array{0: string, 1: EtatDossierType}>
     */
    public static function agentNonHabiliteRevenirArreteProvider(): array
    {
        return [
            'redacteur_non_attribue' => ['reda.k-theur@justice.gouv.fr', EtatDossierType::DOSSIER_OK_VERIFIE],
            'agent_validateur' => ['validateur@justice.gouv.fr', EtatDossierType::DOSSIER_OK_VERIFIE],
        ];
    }

    /**
     * ETQ rédacteur, je ne dois pas pouvoir faire revenir à l'arrêté un dossier qui n'y est pas éligible.
     */
    #[DataProvider('dossierPasEligibleProvider')]
    public function testRevenirArreteKoDossierPasEligible(string $emailAgent, EtatDossierType $etat): void
    {
        $dossier = $this->getDossierParEtat($etat);
        $agent = $this->connexion($emailAgent);

        $this->client->request('POST', "/api/agent/fip6/dossier/{$dossier->getId()}/revenir-arrete");

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());

        /** @var \stdClass $output */
        $output = json_decode($this->client->getResponse()->getContent(), false);

        $this->assertObjectHasProperty('erreur', $output);
        $this->assertEquals("Ce dossier n'est pas éligible pour revenir à l'édition de l'arrêté", $output->erreur);
    }

    /**
     * @return array<string, array{0: string, 1: EtatDossierType}>
     */
    public static function dossierPasEligibleProvider(): array
    {
        return [
            'dossier_a_verifier' => ['redacteur@justice.gouv.fr', EtatDossierType::DOSSIER_OK_A_VERIFIER],
            'dossier_non_finalise' => ['redacteur@justice.gouv.fr', EtatDossierType::DOSSIER_A_INSTRUIRE],
        ];
    }
}
