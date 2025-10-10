<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Command;

use Doctrine\ORM\EntityManagerInterface;
use mikehaertl\pdftk\Pdf;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Repository\BrisPorteRepository;
use MonIndemnisationJustice\Service\DocumentManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'mij:decouper:declar', description: "Temporaire: découper les déclarations d'acceptation")]
class TempDecouperDeclarationAcceptationCommand extends Command
{
    public function __construct(
        protected readonly BrisPorteRepository $dossierRepository,
        protected readonly EntityManagerInterface $em,
        protected readonly DocumentManager $documentManager,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $dossiersPIaSigner = $this->dossierRepository->listerDossierParEtat(EtatDossierType::DOSSIER_OK_A_SIGNER);

        foreach ($dossiersPIaSigner as $dossier) {
            $output->writeln('Dossier '.$dossier->getId());

            $baseDirectory = sys_get_temp_dir();
            $baseChemin = $baseDirectory.'/dossier-'.$dossier->getId();

            $cheminPiSource = $baseChemin.'-pi-source.pdf';
            $cheminPiDestination = $baseChemin.'-pi-destination.pdf';
            $cheminDecla = $baseChemin.'-decla.pdf';
            file_put_contents($cheminPiSource, $this->documentManager->getContenuTexte($dossier->getCourrierDecision()));

            $pdf = new Pdf($cheminPiSource);

            $data = $pdf->getData();

            if (false === $data) {
                throw new \LogicException($pdf->getError());
            }

            $nbPages = intval($pdf->getData()->offsetGet('NumberOfPages'));

            $output->writeln("Nb. pages pour la PI du dossier {$dossier->getId()} : {$nbPages}");

            $pdf = new Pdf($cheminPiSource);
            $result = $pdf->cat(1, $nbPages - 1)->saveAs($cheminPiDestination);

            if (false === $result) {
                throw new \LogicException($pdf->getError());
            }

            $pdf = new Pdf($cheminPiSource);
            $result = $pdf->cat($nbPages, $nbPages)->saveAs($cheminDecla);

            if (false === $result) {
                throw new \LogicException($pdf->getError());
            }

            $this->documentManager->ajouterFichierLocal($dossier, $cheminPiDestination, DocumentType::TYPE_COURRIER_MINISTERE, false);
            $this->documentManager->ajouterFichierLocal($dossier, $cheminDecla, DocumentType::TYPE_COURRIER_REQUERANT, false);

            $output->writeln("PI et décla du dossier {$dossier->getId()} correctement séparées");
            unlink($cheminPiSource);
            unlink($cheminPiDestination);
            unlink($cheminDecla);
        }

        return Command::SUCCESS;
    }
}
