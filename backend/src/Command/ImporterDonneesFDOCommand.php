<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Command;

use MonIndemnisationJustice\Service\DataGouv\ImporteurCompetencesTerritorialesFDO;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'mij:importer:fdo', description: 'Importe les données liées aux FDO : liste des commissariats, gendarmeries et leurs zones de compétence territoriales.')]
class ImporterDonneesFDOCommand extends Command
{
    public function __construct(
        protected readonly ImporteurCompetencesTerritorialesFDO $importeurCompetencesTerritorialesFDO,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $conserverFichier = true;
        $output->write('Import des données de zone de compétence territoriale');
        $totalCompetences = $this->importeurCompetencesTerritorialesFDO->importer($conserverFichier);
        $output->writeln(" <info>OK</info> : $totalCompetences importée(s)");


        return Command::SUCCESS;
    }
}
