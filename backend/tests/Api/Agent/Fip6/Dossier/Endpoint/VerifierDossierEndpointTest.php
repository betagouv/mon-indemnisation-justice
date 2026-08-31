<?php

namespace MonIndemnisationJustice\Tests\Api\Agent\Fip6\Dossier\Endpoint;

use MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier\VerifierDossierEndpoint;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Entity\PieceJointeValidation;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\AbstractEndpointTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(VerifierDossierEndpoint::class)]
class VerifierDossierEndpointTest extends AbstractEndpointTestCase
{
    public function testVerifierDossierAccepteOk(): void
    {
        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_A_VERIFIER);

        $this->connexionAgent($dossier->getRedacteur());

        $this->client->request('POST', "/api/agent/fip6/dossier/{$dossier->getId()}/verifier", [
            'estRecevable' => true,
            'piecesJointes' => [
                $dossier->getPiecesJointes()->first()->getId() => [
                    'estRecevable' => true,
                    'commentaire' => 'Ce document est lisible',
                ],
            ],
        ]);

        $this->assertTrue($this->client->getResponse()->isOk());

        $this->em->refresh($dossier);

        $this->assertEquals(EtatDossierType::DOSSIER_A_INSTRUIRE, $dossier->getEtatDossier()->getEtat());

        $this->assertCount(1, $dossier->getValidations()->toArray());
        $this->assertTrue($dossier->getValidation()->estRecevable());

        $validationPieceJointe = $dossier->getValidation()->getValidationPieceJointe($dossier->getPiecesJointes()->first());

        $this->assertInstanceOf(PieceJointeValidation::class, $validationPieceJointe);
        $this->assertTrue($validationPieceJointe->estRecevable());
        $this->assertEquals('Ce document est lisible', $validationPieceJointe->getCommentaire());
    }

    public function testVerifierDossierRenvoyeOk(): void
    {
        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_A_VERIFIER);

        $this->connexionAgent($dossier->getRedacteur());

        $this->client->request('POST', "/api/agent/fip6/dossier/{$dossier->getId()}/verifier", [
            'estRecevable' => false,
            'commentaire' => "Il n'est pas mentionné que la facture a été réglée",
            'piecesJointes' => [
                $dossier->getPiecesJointes()->first()->getId() => [
                    'estRecevable' => false,
                    'commentaire' => 'Document incomplet : la date de règlement est manquante',
                ],
            ],
        ]);

        $this->assertTrue($this->client->getResponse()->isOk());

        $this->em->refresh($dossier);

        $this->assertEquals(EtatDossierType::DOSSIER_A_COMPLETER, $dossier->getEtatDossier()->getEtat());

        $this->assertCount(1, $dossier->getValidations()->toArray());
        $this->assertFalse($dossier->getValidation()->estRecevable());
        $this->assertEquals("Il n'est pas mentionné que la facture a été réglée", $dossier->getValidation()->getCommentaire());

        $validationPieceJointe = $dossier->getValidation()->getValidationPieceJointe($dossier->getPiecesJointes()->first());

        $this->assertInstanceOf(PieceJointeValidation::class, $validationPieceJointe);
        $this->assertFalse($validationPieceJointe->estRecevable());
        $this->assertEquals('Document incomplet : la date de règlement est manquante', $validationPieceJointe->getCommentaire());
    }

    public function testVerifierDossierKoPasAVerifier(): void
    {
        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_A_INSTRUIRE);

        $this->connexionAgent($dossier->getRedacteur());

        $this->client->request('POST', "/api/agent/fip6/dossier/{$dossier->getId()}/verifier", [
            'estRecevable' => true,
            'piecesJointes' => [
                $dossier->getPiecesJointes()->first()->getId() => [
                    'estRecevable' => true,
                    'commentaire' => 'Ce document est lisible',
                ],
            ],
        ]);

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $this->client->getResponse()->getStatusCode());

        $this->em->refresh($dossier);

        $this->assertEquals(EtatDossierType::DOSSIER_A_INSTRUIRE, $dossier->getEtatDossier()->getEtat());
        $this->assertEmpty($dossier->getValidations());
    }

    public function testVerifierDossierKoPasRedacteur(): void
    {
        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_A_VERIFIER);

        $this->connexion('reda.k-theur@justice.gouv.fr');

        $this->client->request('POST', "/api/agent/fip6/dossier/{$dossier->getId()}/verifier", [
            'estRecevable' => true,
            'piecesJointes' => [
                $dossier->getPiecesJointes()->first()->getId() => [
                    'estRecevable' => true,
                    'commentaire' => 'Ce document est lisible',
                ],
            ],
        ]);

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());

        $this->em->refresh($dossier);

        $this->assertEquals(EtatDossierType::DOSSIER_A_VERIFIER, $dossier->getEtatDossier()->getEtat());
        $this->assertEmpty($dossier->getValidations());
    }
}
