<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Command;

use MonIndemnisationJustice\Service\DataGouv\ImporteurBrigadeGendarmerie;
use MonIndemnisationJustice\Service\DataGouv\ImporteurCompetencesTerritorialesFDO;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'mij:importer:fdo', description: 'Importe les données liées aux FDO : liste des commissariats, gendarmeries et leurs zones de compétence territoriales.')]
class ImporterDonneesFDOCommand extends Command
{
    public function __construct(
        protected readonly ImporteurBrigadeGendarmerie $importeurBrigadeGendarmerie,
        protected readonly ImporteurCompetencesTerritorialesFDO $importeurCompetencesTerritorialesFDO,
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
        $limite = $input->getOption('limite') ? intval($input->getOption('limite')) : null;

        $output->write('Import des données de gendarmeries');
        $totalGendarmeries = $this->importeurBrigadeGendarmerie->importer($conserverFichier, $limite);
        $output->writeln(" <info>OK</info> : $totalGendarmeries importée(s)");

        $output->write('Import des données de zone de compétence territoriale');
        $totalCompetences = $this->importeurCompetencesTerritorialesFDO->importer($conserverFichier, $limite);
        $output->writeln(" <info>OK</info> : $totalCompetences importée(s)");

        return Command::SUCCESS;
    }
}
