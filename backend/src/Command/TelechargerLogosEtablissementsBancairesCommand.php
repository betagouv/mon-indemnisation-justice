<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Command;

use MonIndemnisationJustice\Service\Banque\FichierEtablissementsBancaires;
use MonIndemnisationJustice\Service\Banque\TelechargeurLogosEtablissementsBancaires;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * À lancer en développement pour mettre à jour les logos du référentiel : télécharge chaque `logo_url` du CSV,
 * enregistre le logo dans `public/images/logos/etablissements_bancaires/` et reporte son nom dans la colonne
 * `logo` du CSV. Les fichiers produits sont ensuite à relire, à vérifier avec `bin/verifier-svg` (aussi exécuté en CI), puis
 * à commiter avec le CSV.
 */
#[AsCommand(name: 'mij:etablissements-bancaires:telecharger-logos', description: 'Télécharge les logos des établissements bancaires et met à jour le référentiel CSV')]
class TelechargerLogosEtablissementsBancairesCommand extends Command
{
    public function __construct(
        protected readonly FichierEtablissementsBancaires $fichier,
        protected readonly TelechargeurLogosEtablissementsBancaires $telechargeur,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('fichier', 'f', InputOption::VALUE_REQUIRED, 'Fichier CSV à traiter à la place du fichier par défaut')
            ->addOption('forcer', null, InputOption::VALUE_NONE, 'Re-télécharger les logos déjà présents')
            ->addOption('supprimer-orphelins', null, InputOption::VALUE_NONE, 'Supprimer les fichiers de logo référencés par aucun établissement');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $chemin = $input->getOption('fichier');
        $forcer = $input->getOption('forcer');

        $lignes = $this->fichier->lire($chemin);
        /** @var array<string, ?string> $logosParUrl une même URL n'est téléchargée qu'une fois */
        $logosParUrl = [];
        $erreurs = 0;

        foreach ($lignes as &$ligne) {
            $url = $ligne['logo_url'];

            if (null === $url) {
                $ligne['logo'] = null;
                continue;
            }

            if (!$forcer && null !== $ligne['logo'] && $this->telechargeur->existe($ligne['logo']) && !array_key_exists($url, $logosParUrl)) {
                $logosParUrl[$url] = $ligne['logo'];
            }

            if (!array_key_exists($url, $logosParUrl)) {
                try {
                    $logosParUrl[$url] = $this->telechargeur->telecharger($url);
                    $io->writeln("<info>✓</info> $url → {$logosParUrl[$url]}");
                } catch (\Throwable $e) {
                    $logosParUrl[$url] = null;
                    ++$erreurs;
                    $io->writeln("<error>✗</error> {$e->getMessage()}");
                }
            }

            // En cas d'échec, on conserve le logo précédent s'il existe encore
            $ligne['logo'] = $logosParUrl[$url] ?? (null !== $ligne['logo'] && $this->telechargeur->existe($ligne['logo']) ? $ligne['logo'] : null);
        }
        unset($ligne);

        $this->fichier->ecrire($lignes, $chemin);

        $utilises = array_values(array_unique(array_filter(array_column($lignes, 'logo'))));
        $orphelins = $this->telechargeur->listerOrphelins($utilises);

        if ($input->getOption('supprimer-orphelins')) {
            array_walk($orphelins, fn (string $nom) => $this->telechargeur->supprimer($nom));
        } elseif ([] !== $orphelins) {
            $io->note(count($orphelins).' logo(s) orphelin(s), à supprimer avec --supprimer-orphelins : '.implode(', ', $orphelins));
        }

        $io->success(sprintf(
            '%d établissement(s) avec logo, %d fichier(s) distinct(s), %d erreur(s)',
            count(array_filter(array_column($lignes, 'logo'))),
            count($utilises),
            $erreurs
        ));

        return 0 === $erreurs ? Command::SUCCESS : Command::FAILURE;
    }
}
