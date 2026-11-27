<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Command;

use MonIndemnisationJustice\Service\Banque\ImporteurEtablissementsBancaires;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'mij:importer:etablissements-bancaires', description: 'Importe le référentiel des établissements bancaires (code banque, nom, logo)')]
class ImporterEtablissementsBancairesCommand extends Command
{
    public function __construct(
        protected readonly ImporteurEtablissementsBancaires $importeur,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('fichier', 'f', InputOption::VALUE_REQUIRED, 'Fichier CSV à importer à la place du fichier par défaut');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->write('Import des établissements bancaires');
        $total = $this->importeur->importer($input->getOption('fichier'));
        $output->writeln(" <info>OK</info> : $total importé(s)");

        return Command::SUCCESS;
    }
}
