<?php

namespace MonIndemnisationJustice\Tests\Api\Agent\Fip6;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\EtatDossierType;
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

    protected function getDossierParReference(string $reference): ?Dossier
    {
        return $this->em->getRepository(Dossier::class)->findOneBy(['reference' => $reference]);
    }

    protected function getDossierParEtat(EtatDossierType $etat, int $index = 0): ?Dossier
    {
        $dossiers = $this->em->getRepository(Dossier::class)->listerDossierParEtat($etat);

        return @$dossiers[$index] ?? null;
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
}
