<?php

namespace MonIndemnisationJustice\Tests\Api\Agent\FDO\Endpoint\BrisDePorte;

use MonIndemnisationJustice\Api\Agent\FDO\Endpoint\BrisDePorte\SoumettreDeclarationBrisPorteEndpoint;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\BrouillonDeclarationFDOBrisPorte;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
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
        $gendarme = $this->connexion('gendarme@gendarmerie.interieur.gouv.fr');
        $brouillon = $this->creerBrouillon($gendarme, [
            'estErreur' => 'OUI',
            'dateOperation' => '2025-12-14',
            'descriptionErreur' => null,
            'adresse' => [
                'ligne1' => '127 boulevard des Fleurs',
                'ligne2' => 'Porte B',
                'codePostal' => '75021',
                'localite' => 'PARIS',
            ],
            'procedure' => [
                'numeroProcedure' => 'PRO1653',
                'serviceEnqueteur' => 'GPNV',
                'telephone' => '0123456789',
                'nomMagistrat' => null,
            ],
            'precisionsRequerant' => 'Logement vide lors de la perquisition',
            'coordonneesRequerant' => null,
        ]);

        $id = $brouillon->getId();

        $this->client->request('POST', "/api/agent/fdo/bris-de-porte/{$id}/soumettre");

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $output = json_decode($this->client->getResponse()->getContent(), false);

        $declaration = $this->em->find(DeclarationFDOBrisPorte::class, $output->id);

        $this->assertInstanceOf(DeclarationFDOBrisPorte::class, $declaration);
        $this->assertEquals($gendarme, $declaration->getAgent());

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
