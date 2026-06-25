<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Command;

use MonIndemnisationJustice\Service\DataGouv\ImporteurZonesCompetencesTerritorialesFDO;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'mij:synchroniser:zones_fdo', description: 'Hello PhpStorm')]
class SynchroniserZonesCompetencesTerritorialesFDOCommand extends Command
{
    public function __construct(
        protected readonly ImporteurZonesCompetencesTerritorialesFDO $importeur,
    ) {
        parent::__construct();
    }

    // /api/resources/c53cd4d4-4623-4772-9b8c-bc72a9cdf4c2/data/csv/
    // https://tabular-api.data.gouv.fr/api/resources/c53cd4d4-4623-4772-9b8c-bc72a9cdf4c2/data/csv/

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->importeur->importer();

        return Command::SUCCESS;
    }
}
