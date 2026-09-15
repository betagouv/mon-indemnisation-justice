<?php

namespace MonIndemnisationJustice\Tests\Api\Agent\Fip6\Dossier\Endpoint;

use MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier\RevenirInstructionDossierEndpoint;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\AbstractEndpointTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;

/**
 * Teste le point d'entrée @RevenirInstructionDossierEndpoint de l'API, permettant au rédacteur attribué de faire
 * revenir en instruction un dossier en attente de signature ou d'approbation par le requérant.
 */
#[CoversClass(RevenirInstructionDossierEndpoint::class)]
class RevenirInstructionDossierEndpointTest extends AbstractEndpointTestCase
{
    /**
     * ETQ rédacteur attribué, je dois pouvoir faire revenir à l'instruction un dossier en attente de signature ou
     * d'approbation par le requérant.
     */
    #[DataProvider('dossierEnAttenteRevenirInstructionProvider')]
    public function testRevenirInstructionOk(EtatDossierType $etat, string $emailRedacteur): void
    {
        $redacteur = $this->connexion($emailRedacteur);
        $dossier = $this->getDossierParEtat($etat);

        $this->client->request('POST', "/api/agent/fip6/dossier/{$dossier->getId()}/revenir-instruction");

        $this->assertTrue($this->client->getResponse()->isOk());

        /** @var \stdClass $output */
        $output = json_decode($this->client->getResponse()->getContent(), false);

        $this->assertObjectHasProperty('etat', $output);
        $this->assertObjectHasProperty('etat', $output->etat);

        $this->assertEquals(EtatDossierType::DOSSIER_EN_INSTRUCTION->value, $output->etat->etat);
        $this->assertObjectHasProperty('redacteur', $output->etat);
        $this->assertEquals($redacteur->getId(), $output->etat->redacteur->id);

        $this->em->refresh($dossier);

        $this->assertEquals(EtatDossierType::DOSSIER_EN_INSTRUCTION, $dossier->getEtatDossier()->getEtat());
    }

    /**
     * @return array<string, array{0: EtatDossierType, 1: string}>
     */
    public static function dossierEnAttenteRevenirInstructionProvider(): array
    {
        return [
            'ok_pi_a_signer' => [EtatDossierType::DOSSIER_OK_A_SIGNER, 'redacteur@justice.gouv.fr'],
            'ok_a_accepter' => [EtatDossierType::DOSSIER_OK_A_APPROUVER, 'redacteur@justice.gouv.fr'],
        ];
    }

    /**
     * ETQ agent non habilité (rédacteur non attribué ou agent validateur), je ne dois pas pouvoir faire revenir à
     * l'instruction un dossier en attente de signature, cette action étant réservée au rédacteur attribué.
     */
    #[DataProvider('agentNonHabiliteRevenirInstructionProvider')]
    public function testRevenirInstructionKoPasHabilite(string $emailAgent, EtatDossierType $etat): void
    {
        $dossier = $this->getDossierParEtat($etat);
        $agent = $this->connexion($emailAgent);
        $this->assertNotEquals($agent->getId(), $dossier->getRedacteur()->getId());

        $this->client->request('POST', "/api/agent/fip6/dossier/{$dossier->getId()}/revenir-instruction");

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());

        /** @var \stdClass $output */
        $output = json_decode($this->client->getResponse()->getContent(), false);

        $this->assertObjectHasProperty('erreur', $output);
        $this->assertEquals('Seul le rédacteur attribué peut faire revenir ce dossier en instruction', $output->erreur);
    }

    /**
     * @return array<string, array{0: string, 1: EtatDossierType}>
     */
    public static function agentNonHabiliteRevenirInstructionProvider(): array
    {
        return [
            'redacteur_non_attribue' => ['reda.k-theur@justice.gouv.fr', EtatDossierType::DOSSIER_OK_A_SIGNER],
            'agent_validateur' => ['validateur@justice.gouv.fr', EtatDossierType::DOSSIER_OK_A_SIGNER],
        ];
    }

    /**
     * ETQ rédacteur, je ne dois pas pouvoir faire revenir à l'instruction qui n'y est pas éligible.
     */
    #[DataProvider('dossierPasEligibleProvider')]
    public function testRevenirInstructionKoDossierPasEligible(string $emailAgent, EtatDossierType $etat): void
    {
        $dossier = $this->getDossierParEtat($etat);
        $agent = $this->connexion($emailAgent);

        $this->client->request('POST', "/api/agent/fip6/dossier/{$dossier->getId()}/revenir-instruction");

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());

        /** @var \stdClass $output */
        $output = json_decode($this->client->getResponse()->getContent(), false);

        $this->assertObjectHasProperty('erreur', $output);
        $this->assertEquals("Ce dossier n'est pas éligible pour revenir à l'instruction", $output->erreur);
    }

    /**
     * @return array<string, array{0: string, 1: EtatDossierType}>
     */
    public static function dossierPasEligibleProvider(): array
    {
        return [
            'dossier_rejete' => ['redacteur@justice.gouv.fr', EtatDossierType::DOSSIER_OK_A_INDEMNISER],
            'dossier_non_finalise' => ['redacteur@justice.gouv.fr', EtatDossierType::DOSSIER_A_INSTRUIRE],
        ];
    }
}
