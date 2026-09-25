<?php

namespace MonIndemnisationJustice\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeControllerTest extends WebTestCase
{
    protected KernelBrowser $client;

    public function setUp(): void
    {
        $this->client = self::createClient(['debug' => 0]);
    }

    /**
     * ETQ visiteur, sur la page d'accueil je dois voir les deux tuiles : bris de porte et déni de justice.
     */
    public function testAccueilPresenteLesDeuxBriques(): void
    {
        $crawler = $this->client->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Bienvenue sur Mon Indemnisation Justice');

        $this->assertCount(1, $crawler->selectLink('Déclarer un bris de porte'));
        $this->assertCount(1, $crawler->selectLink('Déclarer un déni de justice'));
    }

    /**
     * ETQ visiteur, la tuile "bris de porte" doit m'amener sur la page dédiée au bris de porte.
     */
    public function testTuileBrisDePorteMeneVersLaPageBrisDePorte(): void
    {
        $crawler = $this->client->request('GET', '/');

        $lien = $crawler->selectLink('Déclarer un bris de porte')->link();

        $this->assertSame('/bris-de-porte/', parse_url($lien->getUri(), PHP_URL_PATH));
    }

    /**
     * ETQ visiteur, dans un environnement où la brique dysfonctionnement est exposée, la tuile "déni de justice" est un
     * lien actif vers "/dysfonctionnement/", sans mention "Bientôt disponible".
     */
    public function testTuileDeniDeJusticeActiveQuandDysfonctionnementExpose(): void
    {
        $crawler = $this->client->request('GET', '/');

        $lien = $crawler->selectLink('Déclarer un déni de justice')->link();

        $this->assertSame('/dysfonctionnement/', parse_url($lien->getUri(), PHP_URL_PATH));
        $this->assertStringNotContainsString('Bientôt disponible', $crawler->text());
    }

    /**
     * ETQ visiteur non authentifié, je dois pouvoir accéder à la page bris de porte qui reprend l'ancienne accueil.
     */
    public function testPageBrisDePorteAccessibleSansAuthentification(): void
    {
        $this->client->request('GET', '/bris-de-porte/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h3', 'Comment utiliser notre service en ligne ?');
    }

    /**
     * ETQ visiteur, `/declarer-un-prejudice` continue d'afficher la page bris de porte (ancienne accueil).
     */
    public function testDeclarerUnPrejudiceAfficheLaPageBrisDePorte(): void
    {
        $this->client->request('GET', '/declarer-un-prejudice');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h3', 'Comment utiliser notre service en ligne ?');
    }
}
