<?php

namespace MonIndemnisationJustice\Tests\Controller\Requerant;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\Usager;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class HomeControllerTest extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
    }

    #[DataProvider('donneesIndex')]
    public function testIndex(string $courriel, bool $enAttenteFinalisation = false)
    {
        $requerant = $this->em->getRepository(Usager::class)->findOneBy(['email' => $courriel]);

        $this->client->loginUser($requerant, 'requerant');

        $this->client->request('GET', '/requerant');

        if ($enAttenteFinalisation) {
            $dossier = $requerant->getDossiers()->filter(fn (Dossier $dossier) => !$dossier->estDepose())->first();
            $this->assertResponseRedirects("/requerant/bris-de-porte/declarer-un-bris-de-porte/{$dossier->getId()}");
        } else {
            $this->assertResponseRedirects('/requerant/mes-demandes');
        }
    }

    public static function donneesIndex()
    {
        return [
            'sans_dossier_a_finaliser' => [
                'ray.keran@courriel.fr', false,
            ],
            'avec_dossier_a_finaliser' => [
                'raquel.randt@courriel.fr', true,
            ],
        ];
    }
}
