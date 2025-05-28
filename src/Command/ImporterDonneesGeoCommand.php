<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Command;

use MonIndemnisationJustice\Service\DataGouv\DataGouvClient;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'mij:importer:geo', description: 'Hello PhpStorm')]
class ImporterDonneesGeoCommand extends Command
{
    public function __construct(
        protected readonly DataGouvClient $dataGouvClient,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->dataGouvClient->importerGeoPays();

        return Command::SUCCESS;
    }
}
