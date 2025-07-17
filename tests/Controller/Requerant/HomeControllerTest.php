<?php

namespace MonIndemnisationJustice\Tests\Controller\Requerant;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\Requerant;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class HomeControllerTest extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
    }

    public function donneesIndex()
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

    /**
     * @dataProvider donneesIndex
     *
     * @return void
     */
    public function testIndex(string $courriel, bool $enAttenteFinalisation = false)
    {
        $requerant = $this->em->getRepository(Requerant::class)->findOneBy(['email' => $courriel]);

        $this->client->loginUser($requerant, 'requerant');

        $this->client->request('GET', '/requerant');

        if ($enAttenteFinalisation) {
            $dossier = $requerant->getDossiers()->filter(fn (BrisPorte $dossier) => !$dossier->estConstitue())->first();
            $this->assertResponseRedirects("/requerant/bris-de-porte/declarer-un-bris-de-porte/{$dossier->getId()}");
        } else {
            $this->assertResponseRedirects('/requerant/mes-demandes');
        }
    }
}
