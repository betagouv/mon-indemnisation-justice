<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Command;

use MonIndemnisationJustice\Service\DataGouv\ImporteurAvocat;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'mij:importer:avocats', description: "Importe les barreaux et l'annuaire des avocats (CNB)")]
class ImporterDonneesAvocatCommand extends Command
{
    public function __construct(
        protected readonly ImporteurAvocat $importeurAvocat,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('conserver-fichier', 'c', InputOption::VALUE_NONE, 'Conserver le fichier téléchargé');
        $this->addOption('limite', 'l', InputOption::VALUE_REQUIRED, "Limiter l'import à ce nombre de lignes");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $conserverFichier = $input->getOption('conserver-fichier');
        // Comparaison stricte à `null` (et non un simple booléen) : la valeur de l'option est la chaîne "0" quand
        // l'appelant passe `--limite 0`, qui est falsy en PHP et serait sinon interprétée comme "pas de limite".
        $limiteOption = $input->getOption('limite');
        $limite = null !== $limiteOption ? intval($limiteOption) : null;

        $output->write('Import des barreaux et avocats');
        $total = $this->importeurAvocat->importer(conserverFichier: $conserverFichier, limite: $limite);
        $output->writeln(" <info>OK</info> : $total importé(s)");

        return Command::SUCCESS;
    }
}
