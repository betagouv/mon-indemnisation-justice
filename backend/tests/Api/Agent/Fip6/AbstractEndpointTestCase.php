<?php

namespace MonIndemnisationJustice\Tests\Api\Agent\Fip6;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\EtatDossierType;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;
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
            $this->connexionAgent($agent);
        }

        return $agent;
    }

    protected function connexionAgent(Agent $agent): void
    {
        $this->client->loginUser($agent, 'agent');
    }

    protected function assertArrayEquals(array $expected, mixed $actual): void
    {
        $divergences = $this->collectDivergences($expected, $actual);

        if ([] === $divergences) {
            $this->assertTrue(true);

            return;
        }

        $differ = new Differ(new UnifiedDiffOutputBuilder(''));
        $lines = [];

        foreach ($divergences as [$path, $exp, $act]) {
            $expStr = json_encode($exp, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $actStr = json_encode($act, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $lines[] = "[{$path}]\n".$differ->diff($expStr, $actStr);
        }

        $this->fail("\n".implode("\n", $lines));
    }

    /** @return array{string, mixed, mixed}[] */
    private function collectDivergences(mixed $expected, mixed $actual, string $path = ''): array
    {
        if (!is_array($expected) || !is_array($actual)) {
            return $expected === $actual ? [] : [[$path ?: 'root', $expected, $actual]];
        }

        $divergences = [];

        foreach ($expected as $key => $value) {
            $currentPath = $path ? "{$path}.{$key}" : (string) $key;
            if (!array_key_exists($key, $actual)) {
                $divergences[] = [$currentPath, $value, '<missing>'];
            } else {
                $divergences = array_merge($divergences, $this->collectDivergences($value, $actual[$key], $currentPath));
            }
        }

        foreach (array_keys($actual) as $key) {
            if (!array_key_exists($key, $expected)) {
                $currentPath = $path ? "{$path}.{$key}" : (string) $key;
                $divergences[] = [$currentPath, '<missing>', $actual[$key]];
            }
        }

        return $divergences;
    }
}
