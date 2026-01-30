<?php

namespace MonIndemnisationJustice\Tests\Api\Agent\FDO\Endpoint\BrisDePorte;

use MonIndemnisationJustice\Api\Agent\FDO\Endpoint\BrisDePorte\SoumettreDeclarationBrisPorteEndpoint;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\BrouillonDeclarationFDOBrisPorte;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\AbstractEndpointTestCase;
use Symfony\Component\HttpFoundation\Response;

/**
 * Teste le point d'entrée @SoumettreDeclarationBrisPorteEndpoint de l'API, permettant à l'agent des FDO de soumettre un
 * brouillon de déclaration de bris de porte.
 *
 * @internal
 *
 * @covers \MonIndemnisationJustice\Api\Agent\FDO\Endpoint\BrisDePorte\SoumettreDeclarationBrisPorteEndpoint
 */
class SoumettreDeclarationBrisPorteEndpointTest extends AbstractEndpointTestCase
{
    /**
     * ETQ agent des FDO, je dois pouvoir soumettre une déclaration de bris porte avec toutes les données essentielles.
     */
    public function testSoumettreOkSansRequerant(): void
    {
        $policier = $this->connexion('policier@interieur.gouv.fr');

        $brouillon = $this->em->getRepository(BrouillonDeclarationFDOBrisPorte::class)->findOneBy(['agent' => $policier]);

        $id = $brouillon->getId();

        $this->client->request('POST', "/api/agent/fdo/bris-de-porte/{$id}/soumettre");

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $output = json_decode($this->client->getResponse()->getContent(), false);

        $declaration = $this->em->find(DeclarationFDOBrisPorte::class, $output->id);
        $this->assertEquals(2, $declaration->getPiecesJointes()->count());

        $pieceJointe = $declaration->getPiecesJointes()->get(0);
        $this->assertInstanceOf(Document::class, $pieceJointe);
        $this->assertEquals(DocumentType::TYPE_PV_FDO, $pieceJointe->getType());

        $pieceJointe = $declaration->getPiecesJointes()->get(1);
        $this->assertInstanceOf(Document::class, $pieceJointe);
        $this->assertEquals(DocumentType::TYPE_PHOTO_FDO, $pieceJointe->getType());

        $this->assertInstanceOf(DeclarationFDOBrisPorte::class, $declaration);
        $this->assertEquals($policier, $declaration->getAgent());

        // Le brouillon doit être supprimé
        $this->assertNull($this->em->find(DeclarationFDOBrisPorte::class, $id));

        // La déclaration doit avoir récupéré les pièces jointes du brouillon
        $this->assertEquals(2, $declaration->getPiecesJointes()->count());
        $this->assertEquals([DocumentType::TYPE_PV_FDO, DocumentType::TYPE_PHOTO_FDO], $declaration->getPiecesJointes()->map(fn (Document $document) => $document->getType())->toArray());
    }

    /**
     * ETQ agent des FDOs, je dois pouvoir soumettre une déclaration de bris porte avec toutes les données essentielles,
     * y compris les coordonnées du requérant.
     */
    public function testSoumettreOkAvecRequerant(): void
    {
        $gendarme = $this->connexion('gendarme@gendarmerie.interieur.gouv.fr');

        $brouillon = $this->em->getRepository(BrouillonDeclarationFDOBrisPorte::class)->findOneBy(['agent' => $gendarme]);

        $id = $brouillon->getId();

        $this->client->request('POST', "/api/agent/fdo/bris-de-porte/{$id}/soumettre");

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $output = json_decode($this->client->getResponse()->getContent(), false);

        $declaration = $this->em->find(DeclarationFDOBrisPorte::class, $output->id);
        $this->assertEquals(2, $declaration->getPiecesJointes()->count());

        $pieceJointe = $declaration->getPiecesJointes()->get(0);
        $this->assertInstanceOf(Document::class, $pieceJointe);
        $this->assertEquals(DocumentType::TYPE_PHOTO_FDO, $pieceJointe->getType());

        $pieceJointe = $declaration->getPiecesJointes()->get(1);
        $this->assertInstanceOf(Document::class, $pieceJointe);
        $this->assertEquals(DocumentType::TYPE_PHOTO_FDO, $pieceJointe->getType());

        $this->assertInstanceOf(DeclarationFDOBrisPorte::class, $declaration);
        $this->assertEquals($gendarme, $declaration->getAgent());

        // Le brouillon doit être supprimé
        $this->assertNull($this->em->find(DeclarationFDOBrisPorte::class, $id));

        // La déclaration doit avoir récupéré les pièces jointes du brouillon
        $this->assertEquals(2, $declaration->getPiecesJointes()->count());
        $this->assertEquals([DocumentType::TYPE_PHOTO_FDO, DocumentType::TYPE_PHOTO_FDO], $declaration->getPiecesJointes()->map(fn (Document $document) => $document->getType())->toArray());
    }

    /**
     * ETQ agent des FDOs, je ne dois pas pouvoir soumettre une déclaration dont la date d'opération est manquante.
     */
    public function testSoumettreKoDateOperationManquante(): void
    {
        $policier = $this->connexion('policier@interieur.gouv.fr');

        $brouillon = $this->creerBrouillon($policier, [
            'estErreur' => 'DOUTE',
            'descriptionErreur' => 'Cassé la porte',
            'adresse' => [
                'ligne1' => '127 boulevard des Fleurs',
                'ligne2' => 'Porte B',
                'codePostal' => '75021',
                'localite' => 'PARIS',
            ],
            'procedure' => [
                'numeroProcedure' => 'PRO1653',
                'serviceEnqueteur' => 'DPNC',
                'telephone' => '0123456789',
                'nomMagistrat' => null,
            ],
            'piecesJointes' => [],
            'precisionsRequerant' => 'Logement vide lors de la perquisition',
            'coordonneesRequerant' => null,
        ]);

        $this->client->request('POST', "/api/agent/fdo/bris-de-porte/{$brouillon->getId()}/soumettre");

        $this->assertTrue($this->client->getResponse()->isClientError());

        ['erreurs' => $erreurs] = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($erreurs);
        $this->assertEquals([
            'dateOperation' => "La date de l'opération ayant conduit au bris de porte est requise",
        ], $erreurs);
    }

    /**
     * ETQ agent des FDOs, je ne dois pas pouvoir soumettre une déclaration dont le nom du service enquêteur n'est pas
     * renseigné.
     */
    public function testSoumettreKoServiceEnqueteurManquant(): void
    {
        $policier = $this->connexion('policier@interieur.gouv.fr');

        $brouillon = $this->creerBrouillon($policier, [
            'estErreur' => 'DOUTE',
            'dateOperation' => (new \DateTime())->sub(\DateInterval::createFromDateString('6 days'))->format('Y-m-d'),
            'descriptionErreur' => 'Cassé la porte',
            'adresse' => [
                'ligne1' => '127 boulevard des Fleurs',
                'ligne2' => 'Porte B',
                'codePostal' => '75021',
                'localite' => 'PARIS',
            ],
            'procedure' => [
                'numeroProcedure' => 'PRO1653',
                'serviceEnqueteur' => null,
                'telephone' => '0123456789',
                'nomMagistrat' => null,
            ],
            'piecesJointes' => [],
            'precisionsRequerant' => 'Logement vide lors de la perquisition',
            'coordonneesRequerant' => null,
        ]);

        $this->client->request('POST', "/api/agent/fdo/bris-de-porte/{$brouillon->getId()}/soumettre");

        $this->assertTrue($this->client->getResponse()->isClientError());

        ['erreurs' => $erreurs] = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($erreurs);
        $this->assertEquals([
            'procedure.serviceEnqueteur' => 'Le nom du service enquêteur est requis',
        ], $erreurs);
    }

    /**
     * ETQ agent des FDOs, je ne dois pas pouvoir soumettre une déclaration dont le courriel n'est pas renseigné.
     */
    public function testSoumettreKoCourrielRequerantManquant(): void
    {
        $gendarme = $this->connexion('gendarme@gendarmerie.interieur.gouv.fr');

        $brouillon = $this->creerBrouillon($gendarme, [
            'estErreur' => 'DOUTE',
            'dateOperation' => (new \DateTime())->sub(\DateInterval::createFromDateString('6 days'))->format('Y-m-d'),
            'descriptionErreur' => 'Trompé de porte',
            'adresse' => [
                'ligne1' => '127 boulevard des Fleurs',
                'codePostal' => '69004',
                'localite' => 'LYON',
            ],
            'procedure' => [
                'numeroProcedure' => 'PRO3491',
                'serviceEnqueteur' => 'GPNV',
                'telephone' => '0234567891',
                'nomMagistrat' => 'MARTEAU',
                'juridictionOuParquet' => 'APPEL PARIS',
            ],
            'precisionsRequerant' => 'Logement vide lors de la perquisition',
            'coordonneesRequerant' => [
                'civilite' => 'MME',
                'nom' => 'RENTE',
                'prenom' => 'Rekke',
                'telephone' => '06 11 11 11 11',
                'courriel' => null,
            ],
        ]);

        $this->client->request('POST', "/api/agent/fdo/bris-de-porte/{$brouillon->getId()}/soumettre");

        $this->assertTrue($this->client->getResponse()->isClientError());

        ['erreurs' => $erreurs] = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($erreurs);
        $this->assertEquals([
            'coordonneesRequerant.courriel' => "L'adresse courriel du requérant est requise",
        ], $erreurs);
    }

    /**
     * ETQ agent des FDOs, je ne dois pas pouvoir soumettre une déclaration dont le courriel du requérant est invalide.
     */
    public function testSoumettreKoCourrielRequerantInvalide(): void
    {
        $gendarme = $this->connexion('gendarme@gendarmerie.interieur.gouv.fr');

        $brouillon = $this->creerBrouillon($gendarme, [
            'estErreur' => 'DOUTE',
            'dateOperation' => (new \DateTime())->sub(\DateInterval::createFromDateString('6 days'))->format('Y-m-d'),
            'descriptionErreur' => 'Trompé de porte',
            'adresse' => [
                'ligne1' => '127 boulevard des Fleurs',
                'codePostal' => '69004',
                'localite' => 'LYON',
            ],
            'procedure' => [
                'numeroProcedure' => 'PRO3491',
                'serviceEnqueteur' => 'GPNV',
                'telephone' => '0234567891',
                'nomMagistrat' => 'MARTEAU',
                'juridictionOuParquet' => 'APPEL PARIS',
            ],
            'precisionsRequerant' => 'Logement vide lors de la perquisition',
            'coordonneesRequerant' => [
                'civilite' => 'MME',
                'nom' => 'RENTE',
                'prenom' => 'Rekke',
                'telephone' => '06 11 11 11 11',
                'courriel' => 'rekke',
            ],
        ]);

        $this->client->request('POST', "/api/agent/fdo/bris-de-porte/{$brouillon->getId()}/soumettre");

        $this->assertTrue($this->client->getResponse()->isClientError());

        ['erreurs' => $erreurs] = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertIsArray($erreurs);
        $this->assertEquals([
            'coordonneesRequerant.courriel' => "L'adresse courriel du requérant est invalide",
        ], $erreurs);
    }

    /**
     * ETQ agent des FDOs, je ne dois pas pouvoir soumettre une déclaration initiée par un autre agent.
     */
    public function testSoumettreKoPasEditeur(): void
    {
        $policier = $this->getAgent('policier@interieur.gouv.fr');
        $this->connexion('gendarme@gendarmerie.interieur.gouv.fr');

        $brouillon = $this->em->getRepository(BrouillonDeclarationFDOBrisPorte::class)->findOneBy(['agent' => $policier]);

        $this->client->request('POST', "/api/agent/fdo/bris-de-porte/{$brouillon->getId()}/soumettre");

        $this->assertTrue(Response::HTTP_FORBIDDEN === $this->client->getResponse()->getStatusCode());
    }

    /**
     * ETQ agent du MJ, je ne dois pas pouvoir soumettre une déclaration de bris de porte.
     */
    public function testSoumettreKoPasFDO(): void
    {
        $policier = $this->getAgent('policier@interieur.gouv.fr');
        $this->connexion('redacteur@justice.gouv.fr');

        $brouillon = $this->em->getRepository(BrouillonDeclarationFDOBrisPorte::class)->findOneBy(['agent' => $policier]);

        $this->client->request('POST', "/api/agent/fdo/bris-de-porte/{$brouillon->getId()}/soumettre");

        $this->assertTrue(Response::HTTP_FORBIDDEN === $this->client->getResponse()->getStatusCode());
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
