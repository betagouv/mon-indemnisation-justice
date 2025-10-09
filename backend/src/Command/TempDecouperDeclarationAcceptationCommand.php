<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Command;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Repository\BrisPorteRepository;
use MonIndemnisationJustice\Service\DocumentManager;
use Smalot\PdfParser\Parser;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'mij:decouper:declaration-acceptation', description: "Temporaire: découper les déclarations d'acceptation")]
class TempDecouperDeclarationAcceptationCommand extends Command
{
    protected Parser $pdfParser;

    public function __construct(
        protected readonly BrisPorteRepository $dossierRepository,
        protected readonly EntityManagerInterface $em,
        protected readonly DocumentManager $documentManager,
    ) {
        parent::__construct();

        $this->pdfParser = new Parser();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dossiersPIaSigner = $this->dossierRepository->listerDossierParEtat(EtatDossierType::DOSSIER_OK_A_SIGNER);

        foreach ($dossiersPIaSigner as $dossier) {
            $output->writeln('Dossier '.$dossier->getId());

            $baseChemin = sys_get_temp_dir().'/dossier-'.$dossier->getId();

            $cheminPi = $baseChemin.'-pi.pdf';

            $cheminDecla = $baseChemin.'-decla.pdf';

            // TODO utiliser pdftk ( pdftk <file> dump_data | grep NumberOfPages et pdftk cat 2_2 output decla.pdf)

            // => https://github.com/mikehaertl/php-pdftk

            $pdf = $this->pdfParser->parseContent($this->documentManager->getContenuTexte($dossier->getCourrierDecision()));

            $output->writeln("Nb. pages pour la PI du dossier {$dossier->getId()} : ".count($pdf->getPages()));

            file_put_contents($cheminDecla, $pdf->getPages()[count($pdf->getPages()) - 1]);

            $output->writeln("Sauvé la décla du dossier {$dossier->getId()} : ".$cheminDecla);
        }

        return Command::SUCCESS;
    }
}
