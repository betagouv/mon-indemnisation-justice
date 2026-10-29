<?php

namespace MonIndemnisationJustice\Tests\Api\Agent\FDO\Endpoint\BrisDePorte;

use MonIndemnisationJustice\Api\Agent\FDO\Endpoint\BrisDePorte\EditerDeclarationBrisPorteEndpoint;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\Civilite;
use MonIndemnisationJustice\Entity\CoordonneesRequerant;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorteErreurType;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\AbstractEndpointTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

/**
 * Teste le point d'entrée @EditerDeclarationBrisPorteEndpoint de l'API, permettant à l'agent des FDO de patcher les données
 * d'un brouillon de déclaration de bris de porte.
 */
#[CoversClass(EditerDeclarationBrisPorteEndpoint::class)]
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
     * ETQ agent des FDO, je peux modifier une déclaration déjà soumise.
     */
    public function testEditerOkDejaSoumise(): void
    {
        $gendarme = $this->connexion('gendarme@gendarmerie.interieur.gouv.fr');
        $brouillon = $this->creerBrouillon($gendarme, DeclarationFDOBrisPorteErreurType::DOUTE, '2025-12-14');
        $brouillon->setDateSoumission(new \DateTimeImmutable());
        $this->em->flush();

        $this->client->request('PATCH', "/api/agent/fdo/bris-de-porte/{$brouillon->getId()}/editer", content: json_encode([
            'estErreur' => 'OUI',
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    /**
     * ETQ agent des FDO, je peux corriger les coordonnées du requérant d'une déclaration déjà soumise.
     */
    public function testEditerOkCoordonneesRequerantDejaSoumise(): void
    {
        $gendarme = $this->connexion('gendarme@gendarmerie.interieur.gouv.fr');
        $declaration = $this->creerDeclarationSoumiseAvecCoordonneesRequerant($gendarme);

        $this->client->request('PATCH', "/api/agent/fdo/bris-de-porte/{$declaration->getId()}/editer", content: json_encode([
            'coordonneesRequerant' => ['courriel' => 'nouveau@courriel.fr'],
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        // Le courriel d'invitation est renvoyé à la nouvelle adresse
        $this->assertEmailCount(1);
        $this->assertEmailAddressContains($this->getMailerMessage(), 'To', 'nouveau@courriel.fr');

        $declaration = $this->em->find(DeclarationFDOBrisPorte::class, $declaration->getId());
        $this->assertEquals('nouveau@courriel.fr', $declaration->getCoordonneesRequerant()?->getCourriel());
        $this->assertEquals('RENTE', $declaration->getCoordonneesRequerant()?->getNom());
    }

    /**
     * ETQ agent des FDO, je ne dois pas pouvoir supprimer les coordonnées du requérant d'une déclaration déjà soumise.
     */
    public function testEditerKoSupprimerCoordonneesRequerantDejaSoumise(): void
    {
        $gendarme = $this->connexion('gendarme@gendarmerie.interieur.gouv.fr');
        $declaration = $this->creerDeclarationSoumiseAvecCoordonneesRequerant($gendarme);

        $this->client->request('PATCH', "/api/agent/fdo/bris-de-porte/{$declaration->getId()}/editer", content: json_encode([
            'coordonneesRequerant' => null,
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        ['erreur' => $erreur] = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals("Les coordonnées du requérant d'une déclaration déjà soumise ne peuvent pas être supprimées", $erreur);

        $this->em->clear();
        $declaration = $this->em->find(DeclarationFDOBrisPorte::class, $declaration->getId());
        $this->assertNotNull($declaration->getCoordonneesRequerant());
        $this->assertEquals('RENTE', $declaration->getCoordonneesRequerant()->getNom());
    }

    /**
     * ETQ agent des FDO, je peux supprimer les coordonnées du requérant d'un brouillon de déclaration.
     */
    public function testEditerOkSupprimerCoordonneesRequerantBrouillon(): void
    {
        $gendarme = $this->connexion('gendarme@gendarmerie.interieur.gouv.fr');
        $brouillon = $this->creerBrouillon($gendarme);
        $brouillon->setCoordonneesRequerant($this->creerCoordonneesRequerant());
        $this->em->flush();

        $this->client->request('PATCH', "/api/agent/fdo/bris-de-porte/{$brouillon->getId()}/editer", content: json_encode([
            'coordonneesRequerant' => null,
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);

        $this->em->clear();
        $brouillon = $this->em->find(DeclarationFDOBrisPorte::class, $brouillon->getId());
        $this->assertNull($brouillon->getCoordonneesRequerant());
    }

    /**
     * ETQ agent des FDO, si je modifie les coordonnées du requérant d'une déclaration déjà soumise sans changer son
     * courriel, l'invitation ne doit pas être renvoyée.
     */
    public function testEditerOkCoordonneesRequerantSansChangementCourrielDejaSoumise(): void
    {
        $gendarme = $this->connexion('gendarme@gendarmerie.interieur.gouv.fr');
        $declaration = $this->creerDeclarationSoumiseAvecCoordonneesRequerant($gendarme);

        $this->client->request('PATCH', "/api/agent/fdo/bris-de-porte/{$declaration->getId()}/editer", content: json_encode([
            'coordonneesRequerant' => ['telephone' => '0611111111'],
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        // Le courriel n'ayant pas changé, l'invitation n'est pas renvoyée
        $this->assertEmailCount(0);
    }

    /**
     * ETQ agent des FDO, si j'ajoute les coordonnées du requérant à une déclaration déjà soumise qui n'en avait pas,
     * le courriel d'invitation doit partir.
     */
    public function testEditerOkAjouterCoordonneesRequerantDejaSoumise(): void
    {
        $gendarme = $this->connexion('gendarme@gendarmerie.interieur.gouv.fr');
        $declaration = $this->creerBrouillon($gendarme, DeclarationFDOBrisPorteErreurType::DOUTE, '2025-12-14');
        $declaration->setDateSoumission(new \DateTimeImmutable());
        $this->em->flush();
        $this->assertNotNull($declaration->getReference());

        $this->client->request('PATCH', "/api/agent/fdo/bris-de-porte/{$declaration->getId()}/editer", content: json_encode([
            'coordonneesRequerant' => [
                'civilite' => 'M',
                'nom' => 'RENTE',
                'prenom' => 'Pierre',
                'telephone' => '0600000000',
                'courriel' => 'pierre.rente@courriel.fr',
            ],
        ]));

        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertEmailCount(1);
        $this->assertEmailAddressContains($this->getMailerMessage(), 'To', 'pierre.rente@courriel.fr');
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

    protected function creerDeclarationSoumiseAvecCoordonneesRequerant(Agent $agent): DeclarationFDOBrisPorte
    {
        $declaration = $this->creerBrouillon($agent, DeclarationFDOBrisPorteErreurType::DOUTE, '2025-12-14')
            ->setCoordonneesRequerant($this->creerCoordonneesRequerant())
            ->setDateSoumission(new \DateTimeImmutable());

        $this->em->flush();
        // Ignorer le courriel d'invitation envoyé lors de la soumission initiale
        static::getContainer()->get('mailer.message_logger_listener')->reset();

        return $declaration;
    }

    protected function creerCoordonneesRequerant(): CoordonneesRequerant
    {
        return new CoordonneesRequerant()
            ->setCivilite(Civilite::M)
            ->setNom('RENTE')
            ->setPrenom('Pierre')
            ->setTelephone('0600000000')
            ->setCourriel('pierre.rente@courriel.fr');
    }
}
