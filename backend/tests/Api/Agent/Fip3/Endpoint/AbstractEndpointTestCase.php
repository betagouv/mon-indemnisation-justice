<?php

namespace MonIndemnisationJustice\Tests\Api\Agent\Fip3\Endpoint;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\BrisPorte;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractEndpointTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;

    public function setUp(): void
    {
        $this->client = self::createClient(['debug' => true]);
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
    }

    abstract protected function getApiRoute(): string;

    protected function getDossierParReference(string $reference): ?BrisPorte
    {
        return $this->em->getRepository(BrisPorte::class)->findOneBy(['reference' => $reference]);
    }

    protected function getAgent(string $aliasOrCourriel): ?Agent
    {
        return $this->em->getRepository(Agent::class)->findOneBy(['email' => $aliasOrCourriel]);
    }

    protected function connexion(string $courriel): ?Agent
    {
        $agent = $this->getAgent($courriel);

        if ($agent) {
            $this->client->loginUser($agent, 'agent');
        }

        return $agent;
    }

    protected function apiRequest(string $method, array|string $contenu = [], array $parametres = []): void
    {
        $this->client->request($method, $this->construireApiRoute($parametres), $contenu);
    }

    protected function apiGet(array $parametres = []): void
    {
        $this->apiRequest('GET', [], $parametres);
    }

    protected function apiPost(array|string $contenu, array $parametresDeRoute = []): void
    {
        $this->apiRequest('POST', $contenu, $parametresDeRoute);
    }

    protected function construireApiRoute(array $parametres = []): string
    {
        $route = $this->getApiRoute();
        foreach ($parametres as $nom => $valeur) {
            $route = preg_replace(sprintf('/{%s}/', $nom), $valeur, $route);
        }

        return $route;
    }
}
