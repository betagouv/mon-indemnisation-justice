<?php

namespace Api\Agent\FDO\Endpoint\BrisDePorte;

use MonIndemnisationJustice\Api\Agent\FDO\Endpoint\BrisDePorte\TeleverserPieceJointeDeclarationBrisPorteEndpoint;
use MonIndemnisationJustice\Entity\BrouillonDeclarationFDOBrisPorte;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\AbstractEndpointTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

/**
 * Teste le point d'entrée @TeleverserPieceJointeDeclarationBrisPorteEndpoint de l'API, qui permet à l'agent des FDO de
 * téléverser un document et l'ajouter en tant que pièce jointe d'un brouillon de déclaration.
 *
 * @internal
 *
 * @covers \MonIndemnisationJustice\Api\Agent\FDO\Endpoint\BrisDePorte\TeleverserPieceJointeDeclarationBrisPorteEndpoint
 */
class TeleverserPieceJointeDeclarationBrisPorteEndpointTest extends AbstractEndpointTestCase
{
    /**
     * ETQ agent des FDO, je dois pouvoir téléverser un document à un brouillon de déclaration.
     */
    public function testTeleverserOk(): void
    {
        $policier = $this->connexion('policier@interieur.gouv.fr');

        $brouillon = $this->em->getRepository(BrouillonDeclarationFDOBrisPorte::class)->findOneBy(['agent' => $policier]);
        $type = DocumentType::TYPE_PHOTO_FDO;

        $this->client->request(
            'POST',
            "/api/agent/fdo/bris-de-porte/{$brouillon->getId()}/piece-jointe/televerser/{$type->value}",
            files: [
                'pieceJointe' => new UploadedFile(dirname(__FILE__).'/../../../../../ressources/pieces_jointes/photo-1.jpg', 'photo-1.jpg', 'image/jpeg'),
            ]
        );

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $input = json_decode($this->client->getResponse()->getContent(), false);

        $this->assertIsObject($input);
        $this->assertObjectHasProperty('piecesJointes', $input);
        $this->assertIsArray($input->piecesJointes);
        $this->assertCount(3, $input->piecesJointes);

        /** @var object $pieceJointe */
        $pieceJointe = $input->piecesJointes[2];

        $this->assertEquals(DocumentType::TYPE_PHOTO_FDO->value, $pieceJointe->type);
        $this->assertEquals('photo-1.jpg', $pieceJointe->originalFilename);
        $this->assertEquals('image/jpeg', $pieceJointe->mime);
    }

    /**
     * ETQ agent du MJ, je ne dois pas pouvoir téléverser un document à ine déclaration d'un agent des FDOs.
     */
    public function testTeleverserKoMauvaisType(): void
    {
        $policier = $this->connexion('policier@interieur.gouv.fr');

        $brouillon = $this->em->getRepository(BrouillonDeclarationFDOBrisPorte::class)->findOneBy(['agent' => $policier]);
        $type = DocumentType::TYPE_CARTE_IDENTITE;
        $this->client->request(
            'POST',
            "/api/agent/fdo/bris-de-porte/{$brouillon->getId()}/piece-jointe/televerser/{$type->value}",
            files: [
                'pieceJointe' => new UploadedFile(dirname(__FILE__).'/../../../../../ressources/pieces_jointes/photo-1.jpg', 'photo-1.jpg', 'image/jpeg'),
            ]
        );

        $this->assertTrue($this->client->getResponse()->isClientError());
    }

    /**
     * ETQ agent du MJ, je ne dois pas avoir accès au téléversement d'un document sur une déclaration d'un agent des
     * FDOs.
     */
    public function testTeleverserKoPasEditeur(): void
    {
        $this->connexion('redacteur@justice.gouv.fr');
        $policier = $this->getAgent('policier@interieur.gouv.fr');

        $brouillon = $this->em->getRepository(BrouillonDeclarationFDOBrisPorte::class)->findOneBy(['agent' => $policier]);
        $type = DocumentType::TYPE_PHOTO_FDO;

        $this->client->request(
            'POST',
            "/api/agent/fdo/bris-de-porte/{$brouillon->getId()}/piece-jointe/televerser/{$type->value}",
            files: [
                'pieceJointe' => new UploadedFile(dirname(__FILE__).'/../../../../../ressources/pieces_jointes/photo-1.jpg', 'photo-1.jpg', 'image/jpeg'),
            ]
        );

        $this->assertTrue(Response::HTTP_FORBIDDEN === $this->client->getResponse()->getStatusCode());
    }
}
