<?php

namespace MonIndemnisationJustice\Tests\Api\Agent\Fip6\Dossier\Endpoint;

use MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier\AttribuerDossierEndpoint;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\APIEndpointTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;

/**
 * Teste le point d'entrée @AttribuerDossierEndpoint de l'API, permettant d'attribuer un dossier à un rédacteur.
 */
#[CoversClass(AttribuerDossierEndpoint::class)]
class AttribuerDossierEndpointTest extends APIEndpointTestCase
{
    /**
     * ETQ agent attributeur, je dois pouvoir être en mesure d'attribuer un dossier à attribuer à un agent rédacteur.
     */
    public function testAttributionOk(): void
    {
        $attributeur = $this->connexion('attributeur@justice.gouv.fr');
        $redacteur = $this->getAgent('redacteur@justice.gouv.fr');
        // $dossier = $this->getDossierParReference('dossier-a-attribuer-melun');
        $dossier = $this->getDossierParReference('BRI/20250410/001');

        $this->attribuer($dossier, $redacteur);

        $this->assertTrue($this->client->getResponse()->isOk());

        /** @var \stdClass $output */
        $output = json_decode($this->client->getResponse()->getContent(), false);

        $this->assertObjectHasProperty('etat', $output);
        $this->assertObjectHasProperty('etat', $output->etat);

        $this->assertEquals(EtatDossierType::DOSSIER_A_INSTRUIRE->value, $output->etat->etat);
        $this->assertObjectHasProperty('redacteur', $output->etat);
        $this->assertEquals($attributeur->getId(), $output->etat->redacteur);
    }

    /**
     * ETQ agent attributeur, si j'essaie d'attribuer un dossier pas à attribuer à un agent rédacteur, je dois obtenir
     * un message d'erreur.
     */
    public function testAttributionKoDossierPasAAttribuer(): void
    {
        $this->connexion('attributeur@justice.gouv.fr');
        $redacteur = $this->getAgent('redacteur@justice.gouv.fr');
        $dossier = $this->getDossierParReference('BRI/20250103/001');

        $this->attribuer($dossier, $redacteur);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());

        /** @var \stdClass $output */
        $output = json_decode($this->client->getResponse()->getContent(), false);

        $this->assertObjectHasProperty('erreur', $output);

        $this->assertEquals("Ce dossier n'est pas à attribuer", $output->erreur);
    }

    /**
     * ETQ agent validateur, si j'essaie d'attribuer un dossier à attribuer à un agent rédacteur, je dois
     * obtenir un message d'erreur.
     */
    public function testAttributionKoAgentPasAttributeur(): void
    {
        $this->connexion('validateur@justice.gouv.fr');
        $redacteur = $this->getAgent('redacteur@justice.gouv.fr');
        $dossier = $this->getDossierParReference('BRI/20250410/001');

        $this->attribuer($dossier, $redacteur);

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());

        /** @var \stdClass $output */
        $output = json_decode($this->client->getResponse()->getContent(), false);

        $this->assertObjectHasProperty('erreur', $output);

        $this->assertEquals('Seul un agent attributeur peut attribuer un dossier', $output->erreur);
    }

    /**
     * ETQ agent attributeur, si j'essaie d'attribuer un dossier à attribuer à un agent psa rédacteur, je dois
     * obtenir un message d'erreur.
     */
    public function testAttributionKoRedacteurPasRedacteur(): void
    {
        $this->connexion('attributeur@justice.gouv.fr');
        $redacteur = $this->getAgent('validateur@justice.gouv.fr');
        $dossier = $this->getDossierParReference('BRI/20250410/001');

        $this->attribuer($dossier, $redacteur);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());

        /** @var \stdClass $output */
        $output = json_decode($this->client->getResponse()->getContent(), false);

        $this->assertObjectHasProperty('erreur', $output);

        $this->assertEquals("Cet agent n'est pas rédacteur", $output->erreur);
    }

    protected function attribuer(Dossier $dossier, Agent $redacteur): void
    {
        $this->apiPost(['redacteur_id' => $redacteur->getId()], ['id' => $dossier->getId()]);
    }

    protected function getApiRoute(): string
    {
        return '/api/agent/fip6/dossier/{id}/attribuer';
    }
}
