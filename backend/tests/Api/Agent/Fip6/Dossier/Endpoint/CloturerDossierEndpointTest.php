<?php

namespace Api\Agent\Fip6\Dossier\Endpoint;

use MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier\CloturerDossierEndpoint;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\AbstractEndpointTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;

/**
 * Teste le point d'entrée @CloturerDossierEndpoint de l'API, permettant d'attribuer un dossier à un rédacteur.
 */
#[CoversClass(CloturerDossierEndpoint::class)]
class CloturerDossierEndpointTest extends AbstractEndpointTestCase
{
    /**
     * ETQ agent attributeur, je dois pouvoir clôturer un dossier clôturable.
     */
    public function testClotureAttributeurOk(): void
    {
        $attributeur = $this->connexion('attributeur@justice.gouv.fr');
        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_A_INSTRUIRE);

        $this->client->request('POST', "/api/agent/fip6/dossier/{$dossier->getId()}/cloturer", [
            'motif' => 'Dossier abandonné',
            'explication' => 'Ce dossier est sans activité de votre part.',
        ]);

        $this->assertTrue($this->client->getResponse()->isOk());

        /** @var \stdClass $output */
        $output = json_decode($this->client->getResponse()->getContent(), false);

        $this->assertObjectHasProperty('etat', $output);
        $this->assertObjectHasProperty('etat', $output->etat);

        $this->assertEquals(EtatDossierType::DOSSIER_CLOTURE->value, $output->etat->etat);
        $this->assertEquals($attributeur->getId(), $output->etat->redacteur);
    }

    /**
     * ETQ agent validateur, je dois pouvoir clôturer un dossier clôturable.
     */
    public function testClotureValidateurOk(): void
    {
        $validateur = $this->connexion('validateur@justice.gouv.fr');
        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_A_INSTRUIRE);

        $this->client->request('POST', "/api/agent/fip6/dossier/{$dossier->getId()}/cloturer", [
            'motif' => 'Dossier abandonné',
            'explication' => 'Ce dossier est sans activité de votre part.',
        ]);

        $this->assertTrue($this->client->getResponse()->isOk());

        /** @var \stdClass $output */
        $output = json_decode($this->client->getResponse()->getContent(), false);

        $this->assertObjectHasProperty('etat', $output);
        $this->assertObjectHasProperty('etat', $output->etat);

        $this->assertEquals(EtatDossierType::DOSSIER_CLOTURE->value, $output->etat->etat);
        $this->assertEquals($validateur->getId(), $output->etat->redacteur);
    }

    /**
     * ETQ agent rédacteur, je dois pouvoir clôturer un dossier clôturable quen j'instruis.
     */
    public function testClotureRedacteurInstructeurOk(): void
    {
        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_A_INSTRUIRE);
        $redacteur = $dossier->getRedacteur();
        $this->client->loginUser($redacteur, 'agent');

        $this->client->request('POST', "/api/agent/fip6/dossier/{$dossier->getId()}/cloturer", [
            'motif' => 'Dossier abandonné',
            'explication' => 'Ce dossier est sans activité de votre part.',
        ]);

        $this->assertTrue($this->client->getResponse()->isOk());

        /** @var \stdClass $output */
        $output = json_decode($this->client->getResponse()->getContent(), false);

        $this->assertObjectHasProperty('etat', $output);
        $this->assertObjectHasProperty('etat', $output->etat);

        $this->assertEquals(EtatDossierType::DOSSIER_CLOTURE->value, $output->etat->etat);
        $this->assertEquals($redacteur->getId(), $output->etat->redacteur);
    }

    /**
     * ETQ agent rédacteur, je ne dois pas pouvoir clôturer un dossier clôturable que je n'instruis pas.
     */
    public function testClotureRedacteurNonInstructeurKo(): void
    {
        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_A_INSTRUIRE);
        $redacteur = $this->connexion('reda.k-theur@justice.gouv.fr');
        $this->assertNotEquals($redacteur->getId(), $dossier->getRedacteur()->getId());

        $this->client->request('POST', "/api/agent/fip6/dossier/{$dossier->getId()}/cloturer", [
            'motif' => 'Dossier abandonné',
            'explication' => 'Ce dossier est sans activité de votre part.',
        ]);

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());

        /** @var \stdClass $output */
        $output = json_decode($this->client->getResponse()->getContent(), false);

        $this->assertObjectHasProperty('erreur', $output);
        $this->assertEquals('Seul un agent autorisé peut clôturer un dossier', $output->erreur);
    }

    /**
     * ETQ agent, je ne dois pas pouvoir clôturer un dossier qui n'est pas clôturable.
     */
    public function testClotureDossierNonCloturableKo(): void
    {
        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_A_FINALISER);
        $validateur = $this->getAgent('validateur@justice.gouv.fr');
        $this->client->loginUser($validateur, 'agent');

        $this->client->request('POST', "/api/agent/fip6/dossier/{$dossier->getId()}/cloturer", [
            'motif' => 'Dossier abandonné',
            'explication' => 'Ce dossier est sans activité de votre part.',
        ]);
        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());

        /** @var \stdClass $output */
        $output = json_decode($this->client->getResponse()->getContent(), false);

        $this->assertObjectHasProperty('erreur', $output);
        $this->assertEquals("Ce dossier n'est pas clôturable", $output->erreur);


    }
}
