<?php

namespace MonIndemnisationJustice\Tests\Api\Agent\FDO\Endpoint\BrisDePorte;

use MonIndemnisationJustice\Api\Agent\FDO\Endpoint\BrisDePorte\SoumettreDeclarationBrisPorteEndpoint;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\BrouillonDeclarationFDOBrisPorte;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\AbstractEndpointTestCase;

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
        $this->assertEquals(1, $declaration->getPiecesJointes()->count());
        $pieceJointe = $declaration->getPiecesJointes()->get(0);
        $this->assertInstanceOf(Document::class, $pieceJointe);
        $this->assertEquals(DocumentType::TYPE_PV_FDO, $pieceJointe->getType());

        $this->assertInstanceOf(DeclarationFDOBrisPorte::class, $declaration);
        $this->assertEquals($policier, $declaration->getAgent());

        // Le brouillon doit être supprimé
        $this->assertNull($this->em->find(DeclarationFDOBrisPorte::class, $id));
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
