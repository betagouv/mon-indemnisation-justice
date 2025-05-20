<?php

namespace MonIndemnisationJustice\Twig;

use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\EtatDossierType;
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

    public function etatDossierRequerant(BrisPorte $dossier): string
    {
        switch ($dossier->getEtatDossier()->getEtat()) {
            case EtatDossierType::DOSSIER_A_FINALISER:
                return 'Dossier à compléter';
            case EtatDossierType::DOSSIER_A_INSTRUIRE:
            case EtatDossierType::DOSSIER_EN_INSTRUCTION:
            case EtatDossierType::DOSSIER_OK_A_SIGNER:
            case EtatDossierType::DOSSIER_KO_A_SIGNER:
                return 'Dossier déposé';
            case EtatDossierType::DOSSIER_OK_A_APPROUVER:
                return 'Indemnisation à accepter';
            case EtatDossierType::DOSSIER_OK_A_INDEMNISER:
            case EtatDossierType::DOSSIER_OK_A_VERIFIER:
                return "En attente d'indemnisation";
            case EtatDossierType::DOSSIER_OK_INDEMNISE:
                return 'Indemnisé';
            case EtatDossierType::DOSSIER_KO_REJETE:
            case EtatDossierType::DOSSIER_CLOTURE:
                return 'Dossier rejeté';
        }

        return '';
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
        $output = str_replace(['$1', '$2', '$3'], [$_1, $_2, $numberParsing[1] > 1 ? 's' : ''], '$1 euros et $2 centime$3');

        return $output;
    }

    public function typesDocument(): array
    {
        return Document::$types;
    }

    public function absoluteAssetPath(string $path): string
    {
        return "file://$this->publicDirectory/$path";
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
