<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Command;

use MonIndemnisationJustice\Service\DataGouv\ImporteurGeoCodePostal;
use MonIndemnisationJustice\Service\DataGouv\ImporteurGeoCommune;
use MonIndemnisationJustice\Service\DataGouv\ImporteurGeoDepartement;
use MonIndemnisationJustice\Service\DataGouv\ImporteurGeoPays;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'mij:importer:geo', description: 'Importe les données géographiques')]
class ImporterDonneesGeoCommand extends Command
{
    public function __construct(
        protected readonly ImporteurGeoPays $importeurGeoPays,
        protected readonly ImporteurGeoDepartement $importeurGeoDepartement,
        protected readonly ImporteurGeoCommune $importeurGeoCommune,
        protected readonly ImporteurGeoCodePostal $importeurGeoCodePostal,
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

        $output->write('Import des données de pays');
        $totalPays = $this->importeurGeoPays->importer(conserverFichier: $conserverFichier, limite: $limite);
        $output->writeln(" <info>OK</info> : $totalPays importé(s)");
        $output->write('Import des données de département');
        $totalDepartements = $this->importeurGeoDepartement->importer(conserverFichier: $conserverFichier, limite: $limite);
        $output->writeln(" <info>OK</info> : $totalDepartements importé(s)");
        $output->write('Import des données de communes');
        $totalCommunes = $this->importeurGeoCommune->importer(conserverFichier: $conserverFichier, limite: $limite);
        $output->writeln(" <info>OK</info> : $totalCommunes importée(s)");

        $output->write('Import des données de codes postaux');
        $totalCodesPostaux = $this->importeurGeoCodePostal->importer(conserverFichier: $conserverFichier, limite: $limite);
        $output->writeln(" <info>OK</info> : $totalCodesPostaux importé(s)");

        return Command::SUCCESS;
    }
}
