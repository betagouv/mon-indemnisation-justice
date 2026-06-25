<?php

namespace Api\Requerant\PieceJointe;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Requerant\PieceJointe\SupprimerPieceJointeEndpoint;
use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\DossierType;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Entity\Usager;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(SupprimerPieceJointeEndpoint::class)]
class SupprimerPieceJointeEndpointTest extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = self::createClient(['debug' => true]);
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
    }

    public function testSupprimerPieceJointeOk()
    {

        /** @var Dossier $dossier */
        $dossier = $this->em->getRepository(Dossier::class)->getDossierParEtat(EtatDossierType::DOSSIER_A_ATTRIBUER, DossierType::BRIS_PORTE);
        $usager = $dossier->getUsager();
        $nbPiecesJointes = $dossier->getPiecesJointes()->count();
        /** @var Document $pieceJointe */
        $pieceJointe = $dossier->getPiecesJointes()->first();
        $this->client->loginUser($usager, 'requerant');

        $this->client->request(
            'DELETE',
            "/api/requerant/piece-jointe/{$pieceJointe->getId()}/supprimer",
        );

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $donnees = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertCount($nbPiecesJointes - 1, $donnees['piecesJointes']);

        $this->em->refresh($dossier);

        $this->assertCount($nbPiecesJointes - 1, $dossier->getPiecesJointes());
        $this->assertEmpty($dossier->getPiecesJointes()->filter(fn (Document $pj) => $pj->getId() === $pieceJointe->getId())->toArray());
    }

    /**
     * ETQ requérant, je ne dois pas pouvoir supprimer la pièce jointe d'un dossier qui n'est pas à moi.
     */
    public function testSupprimerPieceJointeKoPasMonDossier(): void
    {
        /** @var Dossier $dossier */
        $dossier = $this->em->getRepository(Dossier::class)->getDossierParEtat(EtatDossierType::DOSSIER_A_ATTRIBUER, DossierType::BRIS_PORTE);
        $proprietaire = $dossier->getUsager();
        /** @var Document $pieceJointe */
        $pieceJointe = $dossier->getPiecesJointes()->first();

        // Recherchons un usager autre que le propriétaire du dossier
        $autresUsagers = $this->em->getRepository(Usager::class)
            ->createQueryBuilder('u')
            ->where('u.id != :id')
            ->setParameter('id', $proprietaire->getId())
            ->getQuery()
            ->getResult();
        $usager = $autresUsagers[random_int(0, count($autresUsagers) - 1)];

        $this->client->loginUser($usager, 'requerant');

        $this->client->request(
            'DELETE',
            "/api/requerant/piece-jointe/{$pieceJointe->getId()}/supprimer",
        );

        $this->assertEquals(Response::HTTP_FORBIDDEN, $this->client->getResponse()->getStatusCode());

        $donnees = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('Seul le requérant peut supprimer une pièce jointe de son dossier', $donnees['erreur']);
    }
}
