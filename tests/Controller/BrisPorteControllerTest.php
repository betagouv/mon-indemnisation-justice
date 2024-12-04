<?php

namespace App\Tests\Controller;

use App\Forms\TestEligibiliteType;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class BrisPorteControllerTest extends WebTestCase
{
    protected KernelBrowser $client;

    public function setUp(): void
    {
        $this->client = self::createClient();
    }

    /**
     * ETQ visiteur, je dois pouvoir remplir le formulaire de test d'éligibilité.
     *
     * Variantes :
     * * Si j'ai choisi un département hors expérimentation, alors je dois être redirigé vers la page "Contactez-nous"
     * * Si j'ai déjà rempli le questionnaire, alors je dois être automatiquement renvoyé vers la page suivante
     * ("Création de compte" si département en expérimentation, "Contactez-nous" sinon)
     * * Si j'ai déjà rempli mon questionnaire ET créé mon compte, alors je dois être automatiquement renvoyé sur la page
     * "Finaliser la création de votre compte"
     */
    public function testTesterMonEligibilite(): void
    {
        $this->client->request('GET', '/bris-de-porte/tester-mon-eligibilite');
        $this->assertResponseIsSuccessful();
        $form = $this->client->getCrawler()->selectButton("Commencer la demande d'indemnisation")->form();

        $this->client->request($form->getMethod(), $form->getUri(), [
            '_token' => $this->client->getCrawler()->filter('input[name="_token"]')->first()->attr('value'),
            'departement' => '77',
            'estVise' => 'false',
            'estHebergeant' => 'false',
            'estProprietaire' => 'true',
            'aContacteAssurance' => 'false',
        ]);

        $this->assertResponseRedirects('/bris-de-porte/creation-de-compte', 302, 'À la soumission du formulaire, je dois être redirigé vers la page de création de compte');

    }

    /**
     * ETQ visiteur, après avoir rempli le formulaire de test d'éligibilité, si j'ai choisi un département hors
     * expérimentation, je dois être invité à contacter le bureau per courriel.
     */
    public function testContactezNous(): void
    {
        $this->markTestSkipped("Ce test n'est pas implémenté");
    }

    /**
     * ETQ visiteur, après avoir rempli le formulaire de test d'éligibilité, si j'ai choisi un département en
     * expérimentation, je dois être invité à créer mon compte.
     */
    public function testCreationDeCompte(): void
    {
        $this->markTestSkipped("Ce test n'est pas implémenté");
    }

    /**
     * ETQ visiteur, après avoir rempli le formulaire de test d'éligibilité et créé mon compte, je dois être avisé que
     * pour continuer je dois valider mon adresse en cliquant sur le lien figurant dans le courriel que je viens de
     * recevoir.
     */
    public function testFinaliserLaCreation(): void
    {
        $this->markTestSkipped("Ce test n'est pas implémenté");
    }
}
