<?php

namespace Api\Agent\FDO\Endpoint\BrisDePorte;

use MonIndemnisationJustice\Entity\BrouillonDeclarationFDOBrisPorte;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\AbstractEndpointTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Teste le point d'entrée @SupprimerPieceJointeDeclarationBrisPorteEndpoint de l'API, qui permet à l'agent des FDO de
 * supprimer une pièce jointe d'un brouillon de déclaration.
 *
 * @internal
 *
 * @covers \MonIndemnisationJustice\Api\Agent\FDO\Endpoint\BrisDePorte\SupprimerPieceJointeDeclarationBrisPorteEndpoint
 */
class SupprimerPieceJointeDeclarationBrisPorteEndpointTest extends AbstractEndpointTestCase
{
    /**
     * ETQ agent des FDO, je dois pouvoir supprimer une pièce jointe d'un de mes brouillons de déclaration.
     */
    public function testSupprimerPieceJointeOk(): void
    {
        $policier = $this->connexion('policier@interieur.gouv.fr');

        $brouillon = $this->em->getRepository(BrouillonDeclarationFDOBrisPorte::class)->findOneBy(['agent' => $policier]);
        $pieceJointe = $brouillon->getPiecesJointes()[0];

        ['id' => $pieceJointeId, 'fileHash' => $hash] = $pieceJointe;

        $this->client->request(
            'DELETE',
            "/api/agent/fdo/bris-de-porte/{$brouillon->getId()}/piece-jointe/{$pieceJointeId}/{$hash}/supprimer",
        );

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $input = json_decode($this->client->getResponse()->getContent(), false);

        $this->assertIsObject($input);
        $this->assertObjectHasProperty('piecesJointes', $input);
        $this->assertIsArray($input->piecesJointes);
        $this->assertCount(1, $input->piecesJointes);
    }

    /**
     * ETQ agent des FDO, je ne dois pas pouvoir supprimer une pièce jointe d'un de mes brouillons de déclaration si je
     * fournis le mauvais hash.
     */
    public function testSupprimerPieceJointeKoMauvaisHash(): void
    {
        $policier = $this->connexion('policier@interieur.gouv.fr');

        $brouillon = $this->em->getRepository(BrouillonDeclarationFDOBrisPorte::class)->findOneBy(['agent' => $policier]);
        $pieceJointe = $brouillon->getPiecesJointes()[0] ?? [];
        ['id' => $pieceJointeId] = $pieceJointe;
        $hash = 'abc123';

        $this->client->request(
            'DELETE',
            "/api/agent/fdo/bris-de-porte/{$brouillon->getId()}/piece-jointe/{$pieceJointeId}/{$hash}/supprimer",
        );

        $this->assertTrue($this->client->getResponse()->isClientError());

        ['erreur' => $erreur] = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Document inconnu', $erreur);
    }

    /**
     * ETQ agent du MJ, je ne dois pas pouvoir supprimer une pièce jointe d'une déclaration d'un agent des FDOs.
     */
    public function testSupprimerPieceJointeOkPasEditeur(): void
    {
        $this->connexion('redacteur@justice.gouv.fr');
        $policier = $this->getAgent('policier@interieur.gouv.fr');

        $brouillon = $this->em->getRepository(BrouillonDeclarationFDOBrisPorte::class)->findOneBy(['agent' => $policier]);
        $pieceJointe = $brouillon->getPiecesJointes()[0] ?? [];
        ['id' => $pieceJointeId, 'fileHash' => $hash] = $pieceJointe;

        $this->client->request(
            'DELETE',
            "/api/agent/fdo/bris-de-porte/{$brouillon->getId()}/piece-jointe/{$pieceJointeId}/{$hash}/supprimer",
        );

        $this->assertTrue(Response::HTTP_FORBIDDEN === $this->client->getResponse()->getStatusCode());

        ['erreur' => $erreur] = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals("La déclaration d'une erreur opérationnelle est retreinte aux agents des Forces de l'Ordre", $erreur);
    }
}
