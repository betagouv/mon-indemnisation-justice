<?php

declare(strict_types=1);

namespace Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @covers \MonIndemnisationJustice\Controller\SecurityController
 *
 * @internal
 */
class SecurityControllerTest extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
    }

    /**
     * ETQ requérant, si j'essaie de me connecter avec mon adresse courriel à un compte existant avec le bon mot de
     * passe, je dois être redirigé vers l'espace rédacteur.
     */
    public function testConnexionCourrielOk(): void
    {
        $crawler = $this->client->request('GET', '/connexion');

        $form = $crawler->selectButton('Je me connecte à mon espace')->form([
            '_username' => 'raquel.randt@courriel.fr',
            '_password' => 'P4ssword',
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects('/requerant');
    }

    /**
     * ETQ requérant, si j'essaie de me connecter avec mon adresse courriel à un compte existant avec un mot de passe
     * invalide, je ne dois pas pouvoir accéder à l'espace requérant.
     */
    public function testConnexionCourrielKoMauvaisMdP(): void
    {
        $crawler = $this->client->request('GET', '/connexion');

        $form = $crawler->selectButton('Je me connecte à mon espace')->form([
            '_username' => 'raquel.randt@courriel.fr',
            '_password' => 'motDePasseInv4lide',
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects('/connexion');
    }

    /**
     * ETQ requérant, si j'essaie de me connecter avec une adresse courriel qui n'est pas associée à un compte existant,
     * je ne dois pas pouvoir accéder à l'espace requérant.
     */
    public function testConnexionCourrielKoCompteInexistant(): void
    {
        $crawler = $this->client->request('GET', '/connexion');

        $form = $crawler->selectButton('Je me connecte à mon espace')->form([
            '_username' => 'ray.queran@courriel.fr',
            '_password' => 'P4ssword',
        ]);
        $this->client->submit($form);
        $this->assertResponseRedirects('/connexion');
    }
}
