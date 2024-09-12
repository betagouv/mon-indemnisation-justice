<?php

namespace App\Tests\Functional\Requerant;

use Symfony\Component\Panther\DomCrawler\Crawler;
use Symfony\Component\Panther\PantherTestCase;

class DepotBrisPorteTestCase extends PantherTestCase
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
        //$client->takeScreenshot('accueil.png');

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

        $crawler = $client->clickLink('Bris de porte');

        $crawler = $client->waitForVisibility('div[id=react-app]', 3);
        //$client->takeScreenshot('test-eligibilite.png');

        $this->assertSelectorTextContains('#react-app h1', 'Bris de porte');
        // Il ne doit pas y avoir de modale ouverte
        $this->assertSelectorIsNotVisible('dialog');

        $button = $crawler
            ->filter('button')
            ->reduce(function (Crawler $node, $i): bool {
                echo $node->text().PHP_EOL;

                return "Tester mon éligibilité à l'indemnisation" === trim($node->text());
            })
            ->first();
        $this->assertNotNull($button);
        $button->click();

        // La modale doit apparaître
        $this->assertSelectorIsVisible('dialog');
    }
}
