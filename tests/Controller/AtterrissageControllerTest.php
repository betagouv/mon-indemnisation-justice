<?php

namespace MonIndemnisationJustice\Tests\Controller;

use MonIndemnisationJustice\Controller\AtterrissageController;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AtterrissageControllerTest extends WebTestCase
{
    protected KernelBrowser $client;

    public function setUp(): void
    {
        $this->client = self::createClient(['debug' => 0]);
    }


    /**
     * ETQ visiteur, si je scanne le QR code je dois atterrir sur une page dédiée qui me renvoie vers la page d'accueil,
     * avec le drapeau idoine défini en session.
     */
    public function testBrisDePorte(?string $refTestPrecedent = null, ?string $redirection = null, bool $aRequerant = false): void
    {
        $this->client->request('GET', '/atterrissage/bris-de-porte');

        $this->assertTrue($this->client->getResponse()->isRedirect('/'));
        $this->assertTrue($this->client->getRequest()->getSession()->has(AtterrissageController::SESSION_KEY));
    }
}
