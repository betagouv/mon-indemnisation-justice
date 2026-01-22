<?php

namespace MonIndemnisationJustice\Tests\Api\Agent\Fip6;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

abstract class APIEndpointTestCase extends AbstractEndpointTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;

    public function setUp(): void
    {
        $this->client = self::createClient(['debug' => true]);
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
    }

    abstract protected function getApiRoute(): string;

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

    protected function apiPut(array|string $contenu, array $parametresDeRoute = []): void
    {
        $this->apiRequest('PUT', $contenu, $parametresDeRoute);
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
