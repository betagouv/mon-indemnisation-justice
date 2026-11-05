<?php

namespace MonIndemnisationJustice\Service;

use Doctrine\Bundle\FixturesBundle\Command\LoadDataFixturesDoctrineCommand;
use Symfony\Bundle\FrameworkBundle\Routing\Attribute\AsRoutingConditionService;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;

#[AsRoutingConditionService]
class RegistreFonctionnalites
{
    /**
     * Suivi des évènements de courriel par Brevo.
     */
    public const string BREVO = 'brevo';
    /**
     * Envoi des courriels quand un dossier change d'état  (voir `DossierTransitionListener`).
     */
    public const string COURRIEL_DOSSIER = 'courriel_dossier';

    /** Indique si le processus courant charge les données de fixtures (commande `doctrine:fixtures:load`). */
    private bool $chargementFixturesEnCours = false;

    public function __construct(
        protected readonly CollecteurEvenementBrevo $collecteurEvenementBrevo,
        #[Autowire('%env(APP_ENV)')]
        protected readonly string $env,
    ) {
    }

    public function estActive(string $fonctionnalite): bool
    {
        return match ($fonctionnalite) {
            self::BREVO => $this->collecteurEvenementBrevo->estActif(),
            // Lors du chargement des fixtures, les courriels ne partent que si l'environnement l'autorise
            self::COURRIEL_DOSSIER => !$this->chargementFixturesEnCours || 'test' === $this->env,
            default => false,
        };
    }

    #[AsEventListener]
    public function debutCommande(ConsoleCommandEvent $event): void
    {
        if ($event->getCommand() instanceof LoadDataFixturesDoctrineCommand) {
            $this->chargementFixturesEnCours = true;
        }
    }

    #[AsEventListener]
    public function finCommande(ConsoleTerminateEvent $event): void
    {
        if ($event->getCommand() instanceof LoadDataFixturesDoctrineCommand) {
            $this->chargementFixturesEnCours = false;
        }
    }
}
