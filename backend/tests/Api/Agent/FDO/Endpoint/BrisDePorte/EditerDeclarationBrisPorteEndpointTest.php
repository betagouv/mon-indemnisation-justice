<?php

namespace MonIndemnisationJustice\Tests\Api\Agent\FDO\Endpoint\BrisDePorte;

use MonIndemnisationJustice\Api\Agent\FDO\Endpoint\BrisDePorte\EditerDeclarationBrisPorteEndpoint;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorteErreurType;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\AbstractEndpointTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
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
        $this->assertEquals('2025-12-14', $brouillon->getDateOperation()->format('Y-m-d'));
    }

    /**
     * ETQ agent des FDO, je dois pouvoir éditer les données d'un brouillon de déclaration de bris de porte.
     */
    public function testEditerOkRemplacerDateOperation(): void
    {
        $gendarme = $this->connexion('gendarme@gendarmerie.interieur.gouv.fr');
        $brouillon = $this->creerBrouillon($gendarme, dateOperation: '2025-12-15');

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
        $this->assertEquals('2025-12-14', $brouillon->getDateOperation()->format('Y-m-d'));
    }

    /**
     * ETQ agent des FDO, je dois pouvoir éditer partiellement les données imbriquées d'un brouillon de déclaration, sans
     * écraser celles déjà renseignées.
     */
    public function testEditerOkProcedurePartielle(): void
    {
        $gendarme = $this->connexion('gendarme@gendarmerie.interieur.gouv.fr');
        $brouillon = $this->creerBrouillon($gendarme);

        $this->client->request('PATCH', "/api/agent/fdo/bris-de-porte/{$brouillon->getId()}/editer", content: json_encode([
            'procedure' => ['numeroProcedure' => 'PRO123'],
        ]));
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $this->client->request('PATCH', "/api/agent/fdo/bris-de-porte/{$brouillon->getId()}/editer", content: json_encode([
            'procedure' => ['serviceEnqueteur' => 'GPNV'],
            'coordonneesRequerant' => ['nom' => 'RENTE'],
        ]));
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $brouillon = $this->em->find(DeclarationFDOBrisPorte::class, $brouillon->getId());
        $this->assertEquals('PRO123', $brouillon->getProcedure()->getNumeroProcedure());
        $this->assertEquals('GPNV', $brouillon->getProcedure()->getServiceEnqueteur());
        $this->assertEquals('RENTE', $brouillon->getCoordonneesRequerant()?->getNom());
    }

    /**
     * ETQ agent des FDO, je ne dois pas pouvoir modifier une déclaration déjà soumise.
     */
    public function testEditerKoDejaSoumise(): void
    {
        $gendarme = $this->connexion('gendarme@gendarmerie.interieur.gouv.fr');
        $brouillon = $this->creerBrouillon($gendarme, DeclarationFDOBrisPorteErreurType::DOUTE, '2025-12-14');
        $brouillon->setDateSoumission(new \DateTimeImmutable());
        $this->em->flush();

        $this->client->request('PATCH', "/api/agent/fdo/bris-de-porte/{$brouillon->getId()}/editer", content: json_encode([
            'estErreur' => 'OUI',
        ]));

        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
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
        $brouillon = $this->creerBrouillon($gendarme, dateOperation: '2025-12-14');

        $this->client->request('PATCH', "/api/agent/fdo/bris-de-porte/{$brouillon->getId()}/editer", content: json_encode([
            'estErreur' => 'OUI',
            'champInconnu' => 'nimporte quoi',
        ]));

        $this->assertTrue($this->client->getResponse()->isClientError());

        ['erreur' => $erreur] = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Champ non reconnu: champInconnu', $erreur);
    }

    public static function champsLectureSeule(): array
    {
        return [
            'id' => ['id', '01a1862f-0000-7000-8000-000000000001'],
            'reference' => ['reference', 'abcdef'],
            'agent' => ['agent', ['id' => 1]],
            'dateCreation' => ['dateCreation', '2025-12-14T00:00:00+01:00'],
            'dateSoumission' => ['dateSoumission', '2025-12-14T00:00:00+01:00'],
            'piecesJointes' => ['piecesJointes', []],
        ];
    }

    /**
     * ETQ agent des FDO, je ne dois pas pouvoir modifier les champs en lecture seule d'une déclaration, comme son code
     * d'invitation ou sa date de soumission.
     */
    #[DataProvider('champsLectureSeule')]
    public function testEditerKoChampLectureSeule(string $champ, mixed $valeur): void
    {
        $gendarme = $this->connexion('gendarme@gendarmerie.interieur.gouv.fr');
        $brouillon = $this->creerBrouillon($gendarme);

        $this->client->request('PATCH', "/api/agent/fdo/bris-de-porte/{$brouillon->getId()}/editer", content: json_encode([
            $champ => $valeur,
        ]));

        $this->assertTrue($this->client->getResponse()->isClientError());

        ['erreur' => $erreur] = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals("Champ non reconnu: {$champ}", $erreur);

        $this->em->refresh($brouillon);
        $this->assertTrue($brouillon->estBrouillon());
        $this->assertNull($brouillon->getReference());
    }

    /**
     * ETQ agent des FDO, je ne dois pas pouvoir modifier les données de la déclaration d'un autre agent.
     */
    public function testEditerKoPasEditeur(): void
    {
        $gendarme = $this->getAgent('gendarme@gendarmerie.interieur.gouv.fr');

        $brouillon = $this->creerBrouillon($gendarme, DeclarationFDOBrisPorteErreurType::DOUTE, '2025-12-14');

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
        $brouillon = $this->creerBrouillon($gendarme, DeclarationFDOBrisPorteErreurType::DOUTE, '2025-12-14');

        $this->connexion('redacteur@justice.gouv.fr');
        $this->client->request('PATCH', "/api/agent/fdo/bris-de-porte/{$brouillon->getId()}/editer", content: json_encode([
            'estErreur' => 'OUI',
            'dateOperation' => '2025-11-13',
        ]));

        $this->assertTrue($this->client->getResponse()->isClientError());

        ['erreur' => $erreur] = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals("La déclaration d'une erreur opérationnelle est retreinte aux agents des Forces de l'Ordre", $erreur);
    }

    protected function creerBrouillon(Agent $agent, ?DeclarationFDOBrisPorteErreurType $estErreur = null, ?string $dateOperation = null): DeclarationFDOBrisPorte
    {
        $brouillon = new DeclarationFDOBrisPorte()
            ->setAgent($agent)
            ->setEstErreur($estErreur);

        if (null !== $dateOperation) {
            $brouillon->setDateOperation(new \DateTimeImmutable($dateOperation));
        }

        $this->em->persist($brouillon);
        $this->em->flush();

        return $brouillon;
    }
}
