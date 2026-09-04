<?php

namespace MonIndemnisationJustice\Tests\Api\Agent\Fip6\Dossier\Endpoint;

use MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier\DeciderDossierEndpoint;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\AbstractEndpointTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;

/**
 * Teste le point d'entrée @DeciderDossierEndpoint de l'API, permettant au rédacteur en charge de l'instruction
 * d'accepter ou de rejeter la demande d'indemnisation d'un dossier.
 */
#[CoversClass(DeciderDossierEndpoint::class)]
class DeciderDossierEndpointTest extends AbstractEndpointTestCase
{
    /**
     * ETQ rédacteur instructeur, je dois pouvoir accepter la demande d'indemnisation d'un dossier en instruction.
     */
    public function testDeciderAccepterOk(): void
    {
        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_EN_INSTRUCTION);
        $redacteur = $dossier->getRedacteur();
        $this->connexionAgent($redacteur);

        $this->client->request('POST', "/api/agent/fip6/dossier/{$dossier->getId()}/decider", [
            'montantIndemnisation' => 1234.56,
        ]);

        $this->assertTrue($this->client->getResponse()->isOk());

        $this->em->refresh($dossier);

        $this->assertEquals(EtatDossierType::DOSSIER_OK_A_SIGNER, $dossier->getEtatDossier()->getEtat());
        $this->assertEquals($redacteur->getId(), $dossier->getEtatDossier()->getAgent()->getId());
        $this->assertEquals(1234.56, $dossier->getEtatDossier()->getContexte()['montantIndemnisation']);
    }

    /**
     * ETQ rédacteur instructeur, je dois pouvoir rejeter la demande d'indemnisation d'un dossier en instruction.
     */
    public function testDeciderRejeterOk(): void
    {
        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_EN_INSTRUCTION);
        $redacteur = $dossier->getRedacteur();
        $this->connexionAgent($redacteur);

        $this->client->request('POST', "/api/agent/fip6/dossier/{$dossier->getId()}/decider", [
            'motifRejet' => 'LOCATAIRE',
        ]);

        $this->assertTrue($this->client->getResponse()->isOk());

        $this->em->refresh($dossier);

        $this->assertEquals(EtatDossierType::DOSSIER_KO_A_SIGNER, $dossier->getEtatDossier()->getEtat());
        $this->assertEquals($redacteur->getId(), $dossier->getEtatDossier()->getAgent()->getId());
    }

    /**
     * ETQ rédacteur instructeur, je ne dois pas pouvoir décider sans indiquer ni montant ni motif de rejet.
     */
    public function testDeciderKoAucunChoix(): void
    {
        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_EN_INSTRUCTION);
        $this->connexionAgent($dossier->getRedacteur());

        $this->client->request('POST', "/api/agent/fip6/dossier/{$dossier->getId()}/decider", [
            'commentaire' => 'Ni montant ni motif de rejet renseigné',
        ]);

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $this->client->getResponse()->getStatusCode());
    }

    /**
     * ETQ rédacteur instructeur, je ne dois pas pouvoir décider en indiquant à la fois un montant et un motif de rejet.
     */
    public function testDeciderKoChoixMultiple(): void
    {
        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_EN_INSTRUCTION);
        $this->connexionAgent($dossier->getRedacteur());

        $this->client->request('POST', "/api/agent/fip6/dossier/{$dossier->getId()}/decider", [
            'montantIndemnisation' => 1234.56,
            'motifRejet' => 'LOCATAIRE',
        ]);

        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $this->client->getResponse()->getStatusCode());
    }

    /**
     * ETQ rédacteur pas attribué à ce dossier, je ne dois pas pouvoir décider de ce dossier.
     */
    public function testDeciderKoRedacteurNonInstructeur(): void
    {
        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_EN_INSTRUCTION);
        $autreRedacteur = $this->connexion('reda.k-theur@justice.gouv.fr');
        $this->assertNotEquals($autreRedacteur->getId(), $dossier->getRedacteur()->getId());

        $this->client->request('POST', "/api/agent/fip6/dossier/{$dossier->getId()}/decider", [
            'montantIndemnisation' => 1234.56,
        ]);

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());

        /** @var \stdClass $output */
        $output = json_decode($this->client->getResponse()->getContent(), false);

        $this->assertObjectHasProperty('erreur', $output);
        $this->assertEquals("Seul l'agent rédacteur attribué peut instruire un dossier", $output->erreur);
    }
}
