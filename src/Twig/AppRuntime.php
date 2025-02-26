<?php

namespace MonIndemnisationJustice\Twig;

use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\Requerant;
use Pentatrion\ViteBundle\Exception\EntrypointNotFoundException;
use Pentatrion\ViteBundle\Service\EntrypointsLookup;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Security\Core\User\UserInterface;
use Twig\Extension\RuntimeExtensionInterface;

class AppRuntime implements RuntimeExtensionInterface
{
    protected string $publicDirectory;

    public function __construct(
        protected readonly EntrypointsLookup $entrypointLookup,
        #[Autowire(param: 'kernel.project_dir')]
        string $projectDirectory,
    ) {
        $this->publicDirectory = "$projectDirectory/public";
    }

    public function estRequerant(?UserInterface $user = null): bool
    {
        return $user instanceof Requerant;
    }

    public function estAgent(?UserInterface $user = null): bool
    {
        return $user instanceof Agent;
    }

    public function toSnake(string $string): string
    {
        return preg_replace('/(?<=\\w)(?=[A-Z])|(?<=[a-z])(?=[0-9])/', '_', $string);
    }

    public function toKebab(string $string): string
    {
        return preg_replace('/_/', '-', $this->toSnake($string));
    }

    public function montantLitteral(float $amount, string $locale = 'fr'): string
    {
        $t = new \NumberFormatter($locale, \NumberFormatter::SPELLOUT);
        $numberParsing = explode('.', number_format(round($amount, 2, PHP_ROUND_HALF_DOWN), 2, '.', ''));
        $_1 = $t->format($numberParsing[0]);
        $_2 = $t->format($numberParsing[1]);
        $output = str_replace(['$1', '$2'], [$_1, $_2], '$1 euros et $2 centimes');

        return $output;
    }

    public function typesDocument(): array
    {
        return Document::$types;
    }

    public function estViteServerActif(): bool
    {
        return null !== $this->entrypointLookup->getViteServer();
    }

    public function viteEntreeExiste(string $entree): bool
    {
        try {
            return !empty($this->entrypointLookup->getJSFiles($entree));
        } catch (EntrypointNotFoundException $e) {
            return false;
        }
    }

    public function base64Image(string $path)
    {
        if (file_exists("$this->publicDirectory/$path")) {
            return base64_encode(file_get_contents("$this->publicDirectory/$path"));
        }

        return '';
    }
}
