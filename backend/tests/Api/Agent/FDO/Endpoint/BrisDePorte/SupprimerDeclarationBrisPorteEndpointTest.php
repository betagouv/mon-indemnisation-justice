<?php

namespace Api\Agent\FDO\Endpoint\BrisDePorte;

use MonIndemnisationJustice\Api\Agent\FDO\Endpoint\BrisDePorte\SupprimerDeclarationBrisPorteEndpoint;
use MonIndemnisationJustice\Entity\BrouillonDeclarationFDOBrisPorte;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\AbstractEndpointTestCase;

/**
 * Teste le point d'entrée @SupprimerDeclarationBrisPorteEndpoint de l'API, qui donne le droit à l'agent des FDOs à
 * l'origine d'un brouillon de déclaration de bris de porte de le supprimer.
 *
 * @internal
 *
 * @covers \MonIndemnisationJustice\Api\Agent\FDO\Endpoint\BrisDePorte\SupprimerDeclarationBrisPorteEndpoint
 */
class SupprimerDeclarationBrisPorteEndpointTest extends AbstractEndpointTestCase
{
    /**
     * ETQ agent des FDO, je dois pouvoir supprimer un brouillon de déclaration que j'ai précédemment créé.
     */
    public function testSupprimerOk(): void
    {
        $policier = $this->connexion('policier@interieur.gouv.fr');

        $brouillon = $this->em->getRepository(BrouillonDeclarationFDOBrisPorte::class)->findOneBy([
            'agent' => $policier,
        ]);

        $id = $brouillon->getId();

        $this->client->request('DELETE', "/api/agent/fdo/bris-de-porte/{$id}/supprimer");

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        // Le brouillon doit être supprimé
        $this->assertNull($this->em->find(DeclarationFDOBrisPorte::class, $id));
    }
}
