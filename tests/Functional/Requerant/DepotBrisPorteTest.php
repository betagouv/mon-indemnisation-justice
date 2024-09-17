<?php

namespace App\Tests\Functional\Requerant;

use Symfony\Component\Panther\DomCrawler\Crawler;
use Symfony\Component\Panther\PantherTestCase;

class DepotBrisPorteTest extends PantherTestCase
{
    public function testDepotDossierBrisPorte(): void
    {
        // your app is automatically started using the built-in web server
        $client = static::createPantherClient(
            [
                'browser' => PantherTestCase::FIREFOX,
                'env' => [
                    'BASE_URL' => 'http://127.0.0.1:9080/',
                ],
            ],
            [],
            [
                'capabilities' => [
                    'goog:loggingPrefs' => [
                        'browser' => 'ALL', // calls to console.* methods
                        'performance' => 'ALL', // performance data
                    ],
                ],
            ]);
        $client->request('GET', '/');
        //$this->assertResponseIsSuccessful();
        //$client->takeScreenshot('var/screenshots/accueil.png');

        $client->waitForVisibility('main', 3);

        $this->assertSelectorTextContains('main h1', "Nous vous aidons dans votre demande d'indemnisation");
        /*
        $link = $crawler
            ->filter('a')
            ->reduce(function (Crawler $node, $i): bool {
                return trim($node->text()) === "Bris de porte";
            })
            ->first();
        dump($link);

        $this->assertNotNull($link);
        */

        $client->clickLink('Bris de porte');
        //$this->assertResponseIsSuccessful();

        $crawler = $client->waitForVisibility('#page-content', 3);
        $client->takeScreenshot('var/screenshots/test-eligibilite.png');

        $this->assertSelectorTextContains('#page-content h1', 'Bris de porte');
        // Il ne doit pas y avoir de modales ouvertes
        $this->assertSelectorIsNotVisible('dialog');

        $button = $crawler
            ->filter('button')
            ->reduce(function (Crawler $node, $i): bool {
                return "Tester mon éligibilité à l'indemnisation" === trim($node->text());
            })
            ->first();
        $this->assertNotNull($button);
        $button->click();

        // La modale doit apparaître
        $crawler = $client->waitForVisibility('dialog');

        /*
        $form = $crawler->filter('dialog form')->first();

        $this->assertNotNull($form);

        $labels = $form->filter('label,legend');

        $data = [
            "Date de l'opération de police judiciaire" => "2023-12-31",
            "Numéro de procès-verbal" => "PV44",
            "S'agit-il d'une erreur des services de police ?" => "Oui"
        ];

        $fields = array_merge(
            ...array_filter(
                $labels->each(
                    function(Crawler $label) use ($data) {
                        if (isset($data[trim($label->text())])) {
                            echo "Found label {$label->text()}\n";
                            return [
                                trim($label->text()), $label->closest('input,textarea,select')->first()];
                        }
                        return null;
                    }
                )
            )
        );

        dump($fields);
        */

        /*
        foreach ($fields as $field) {
            if ($field->count() > 0) {
                    if ($input->eq(0)->getTagName() !== 'input') {
                        if ($input->eq(0)->getAttribute('type') !== 'radio') {
                            $input->sendKeys($data[trim($label->text())]);
                        } else {
                            $input
                                ->filter("label")
                                ->reduce(function (Crawler $node, $i) use($data, $label): bool {
                                    return trim($node->text()) === $data[trim($label->text())];
                                })
                                ->first()
                                ?->click()
                            ;
                        }
                    }
                    dump($input->getAttribute('value'));
                }
        }
        */

        // Trouver le formulaire
        // Trouver l'input ou

        //$crawler->filter('input[name=dateOperationPJ]')->first()->sendKeys("04");
        //$client->executeScript("document.querySelector('input[name=dateOperationPJ]').click()");
        //$client->executeScript("document.querySelector('input[name=dateOperationPJ]').setAttribute('value', '04092024')");
        //$client->executeScript("document.querySelector('input[name=numeroPV]').setAttribute('value', 'PV45')");
        //$client->executeScript("document.querySelector('input[type=radio][name=isErreurPorte][value=true]').click()");

        /*$crawler->filter("button")
            ->reduce(function (Crawler $node, $i) : bool {
                    return trim($node->text()) === "Vérifier mon éligibilité à l'indemnisation";
            })
            ->first()
            ->click()
        ;*/

        $client->takeScreenshot('var/screenshots/formulaire.png');

        $button = $crawler->selectButton("Vérifier mon éligibilité à l'indemnisation")->first();
        $form = $button->form([
            "dateOperationPJ" => "2023-12-31",
            "numeroPV" => "PV44",
            "isErreurPorte" => "true"
        ]);

        $client->takeScreenshot('var/screenshots/formulaire-apres.png');

        $crawler = $client->submit($form);

        //$crawler = $client->waitForVisibility('dialog#modale-test-eligibilite h3', 2);
        $client->takeScreenshot('var/screenshots/formulaire-submit.png');

        $this->assertNotNull(
            $client
                ->getCrawler()
                ->filter('dialog h3.fr-alert__title')
                ->reduce(function ($e) {
                    return $e->isDisplayed() && "Vous êtes éligible à l'indemnisation";
                })
                ->first()
        );

        $button = $crawler->selectButton("Accéder au formulaire de demande d'indemnisation")->first();
        $this->assertNotNull($button);

        $button->click();

        $client->takeScreenshot('var/screenshots/redirection.png');
    }
}
