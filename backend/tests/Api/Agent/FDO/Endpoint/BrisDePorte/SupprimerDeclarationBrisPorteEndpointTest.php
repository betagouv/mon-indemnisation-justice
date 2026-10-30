<?php

namespace MonIndemnisationJustice\Tests\Api\Agent\FDO\Endpoint\BrisDePorte;

use MonIndemnisationJustice\Api\Agent\FDO\Endpoint\BrisDePorte\SupprimerDeclarationBrisPorteEndpoint;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use MonIndemnisationJustice\Tests\Api\Agent\Fip6\AbstractEndpointTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Teste le point d'entrée @SupprimerDeclarationBrisPorteEndpoint de l'API, qui donne le droit à l'agent des FDOs à
 * l'origine d'un brouillon de déclaration de bris de porte de le supprimer.
 */
#[CoversClass(SupprimerDeclarationBrisPorteEndpoint::class)]
class SupprimerDeclarationBrisPorteEndpointTest extends AbstractEndpointTestCase
{
    /**
     * ETQ agent des FDO, je dois pouvoir supprimer un brouillon de déclaration que j'ai précédemment créé.
     */
    public function testSupprimerOk(): void
    {
        $policier = $this->connexion('policier@interieur.gouv.fr');

        $brouillon = $this->em->getRepository(DeclarationFDOBrisPorte::class)->findOneBy([
            'agent' => $policier,
            'dateSoumission' => null,
        ]);

        $id = $brouillon->getId();

        $this->client->request('DELETE', "/api/agent/fdo/bris-de-porte/{$id}/supprimer");

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        // Le brouillon doit être supprimé
        $this->assertNull($this->em->find(DeclarationFDOBrisPorte::class, $id));
    }

    /**
     * ETQ agent des FDO, je ne dois pas pouvoir supprimer une déclaration que j'ai déjà soumise.
     */
    public function testSupprimerKoDejaSoumise(): void
    {
        $policier = $this->connexion('policier@interieur.gouv.fr');

        $declaration = array_values(array_filter(
            $this->em->getRepository(DeclarationFDOBrisPorte::class)->findBy(['agent' => $policier]),
            fn (DeclarationFDOBrisPorte $d) => !$d->estBrouillon()
        ))[0];

        $this->client->request('DELETE', "/api/agent/fdo/bris-de-porte/{$declaration->getId()}/supprimer");

        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
        $this->assertNotNull($this->em->find(DeclarationFDOBrisPorte::class, $declaration->getId()));
    }
}
