<?php

namespace MonIndemnisationJustice\Tests\Api\Agent\FDO\Endpoint\BrisDePorte;

use MonIndemnisationJustice\Api\Agent\FDO\Endpoint\BrisDePorte\EditerDeclarationBrisPorteEndpoint;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\BrouillonDeclarationFDOBrisPorte;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\AbstractEndpointTestCase;
use Symfony\Component\Uid\Uuid;

/**
 * Teste le point d'entrée @EditerDeclarationBrisPorteEndpoint de l'API, permettant à l'agent des FDO de patcher les données
 * d'un brouillon de déclaration de bris de porte.
 *
 * @internal
 *
 * @covers \MonIndemnisationJustice\Api\Agent\FDO\Endpoint\BrisDePorte\EditerDeclarationBrisPorteEndpoint
 */
class EditerDeclarationBrisPorteEndpointTest extends AbstractEndpointTestCase
{
    /**
     * ETQ agent des FDO, je dois pouvoir éditer les données d'un brouillon de déclaration de bris de porte.
     */
    public function testEditerOkDateOperation(): void
    {
        $gendarme = $this->connexion('gendarme@gendarmerie.interieur.gouv.fr');
        $brouillon = $this->creerBrouillon($gendarme);

        $this->client->request('PATCH', "/api/agent/fdo/bris-de-porte/{$brouillon->getId()}/editer", content: json_encode([
            'dateOperation' => '2025-12-14',
        ]));

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $input = json_decode($this->client->getResponse()->getContent(), false);

        $this->assertIsObject($input);
        $this->assertObjectHasProperty('id', $input);

        $this->assertTrue(Uuid::isValid($input->id));
        $this->assertEquals($brouillon->getId(), $input->id);

        $this->em->refresh($brouillon);
        $input = $brouillon->getDonnees();

        $this->assertArrayHasKey('dateOperation', $input);
        $this->assertEquals($input['dateOperation'], '2025-12-14');
    }

    /**
     * ETQ agent des FDO, je dois pouvoir éditer les données d'un brouillon de déclaration de bris de porte.
     */
    public function testEditerOkRemplacerDateOperation(): void
    {
        $gendarme = $this->connexion('gendarme@gendarmerie.interieur.gouv.fr');
        $brouillon = $this->creerBrouillon($gendarme, [
            'dateOperation' => '2025-12-15',
        ]);

        $this->client->request('PATCH', "/api/agent/fdo/bris-de-porte/{$brouillon->getId()}/editer", content: json_encode([
            'dateOperation' => '2025-12-14',
        ]));

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $input = json_decode($this->client->getResponse()->getContent(), false);

        $this->assertIsObject($input);
        $this->assertObjectHasProperty('id', $input);

        $this->assertTrue(Uuid::isValid($input->id));
        $this->assertEquals($brouillon->getId(), $input->id);

        $this->em->refresh($brouillon);
        $input = $brouillon->getDonnees();

        $this->assertArrayHasKey('dateOperation', $input);
        $this->assertEquals($input['dateOperation'], '2025-12-14');
    }

    public function testEditerKoDateOperationInvalide(): void
    {
        $gendarme = $this->connexion('gendarme@gendarmerie.interieur.gouv.fr');
        $brouillon = $this->creerBrouillon($gendarme);

        $this->client->request('PATCH', "/api/agent/fdo/bris-de-porte/{$brouillon->getId()}/editer", content: json_encode([
            'dateOperation' => '14/12/2025',
        ]));

        $this->assertTrue($this->client->getResponse()->isClientError());
    }

    /**
     * ETQ agent des FDO, si j'envoie des données.
     */
    public function testEditerKoChampInconnu(): void
    {
        $gendarme = $this->connexion('gendarme@gendarmerie.interieur.gouv.fr');
        $brouillon = $this->creerBrouillon($gendarme, [
            'dateOperation' => '2025-12-14',
        ]);

        $this->client->request('PATCH', "/api/agent/fdo/bris-de-porte/{$brouillon->getId()}/editer", content: json_encode([
            'estErreur' => 'OUI',
            'champInconnu' => 'nimporte quoi',
        ]));

        $this->assertTrue($this->client->getResponse()->isClientError());

        ['erreur' => $erreur] = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Champ non reconnu: champInconnu', $erreur);
    }

    /**
     * ETQ agent des FDO, je ne dois pas pouvoir modifier les données de la déclaration d'un autre agent.
     */
    public function testEditerKoPasEditeur(): void
    {
        $gendarme = $this->getAgent('gendarme@gendarmerie.interieur.gouv.fr');

        $brouillon = $this->creerBrouillon($gendarme, [
            'estErreur' => 'DOUTE',
            'dateOperation' => '2025-12-14',
        ]);

        $this->connexion('policier@interieur.gouv.fr');
        $this->client->request('PATCH', "/api/agent/fdo/bris-de-porte/{$brouillon->getId()}/editer", content: json_encode([
            'estErreur' => 'OUI',
            'dateOperation' => '2025-11-13',
        ]));

        $this->assertTrue($this->client->getResponse()->isClientError());

        ['erreur' => $erreur] = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals("La déclaration d'une erreur opérationnelle est retreinte aux agents des Forces de l'Ordre", $erreur);
    }

    /**
     * ETQ agent du MJ, je ne dois pas pouvoir modifier les données de la déclaration d'un agent FDO.
     */
    public function testEditerKoPasFDO(): void
    {
        $gendarme = $this->getAgent('gendarme@gendarmerie.interieur.gouv.fr');
        $brouillon = $this->creerBrouillon($gendarme, [
            'estErreur' => 'DOUTE',
            'dateOperation' => '2025-12-14',
        ]);

        $this->connexion('redacteur@justice.gouv.fr');
        $this->client->request('PATCH', "/api/agent/fdo/bris-de-porte/{$brouillon->getId()}/editer", content: json_encode([
            'estErreur' => 'OUI',
            'dateOperation' => '2025-11-13',
        ]));

        $this->assertTrue($this->client->getResponse()->isClientError());

        ['erreur' => $erreur] = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals("La déclaration d'une erreur opérationnelle est retreinte aux agents des Forces de l'Ordre", $erreur);
    }

    protected function creerBrouillon(Agent $agent, array $donnees = []): BrouillonDeclarationFDOBrisPorte
    {
        $brouillon = (new BrouillonDeclarationFDOBrisPorte())
            ->setAgent($agent)
            ->setDonnees($donnees)
        ;

        $this->em->persist($brouillon);
        $this->em->flush();

        return $brouillon;
    }
}
