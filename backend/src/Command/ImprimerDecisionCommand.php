<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Command;

use MonIndemnisationJustice\Entity\DossierType;
use MonIndemnisationJustice\Repository\DossierRepository;
use MonIndemnisationJustice\Service\DocumentManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'mij:imprimer:decision', description: "Imprimer en PDF le courrier de décision d'un dossier")]
class ImprimerDecisionCommand extends Command
{
    public function __construct(
        protected readonly DossierRepository $dossierRepository,
        protected readonly DocumentManager $documentManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('dossier', InputArgument::REQUIRED, 'Identifiant ou référence du dossier')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Réimprimer même si la décision a déjà été signée (le PDF signé sera remplacé)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $idOuReference = $input->getArgument('dossier');

        $dossier = $this->dossierRepository->getByIdOuReference($idOuReference, DossierType::BRIS_PORTE);

        if (null === $dossier) {
            $io->error("Aucun dossier trouvé pour l'identifiant ou la référence '{$idOuReference}'");

            return Command::FAILURE;
        }

        if (!$dossier->estASigner()) {
            $io->error("Le dossier {$dossier->getReference()} n'est pas en attente de signature");

            return Command::FAILURE;
        }

        $courrier = $dossier->getCourrierDecision();

        if (null === $courrier) {
            $io->error("Le dossier {$dossier->getReference()} n'a pas de courrier de décision");

            return Command::FAILURE;
        }

        $ancienFichier = $courrier->getFilename();

        try {
            $this->documentManager->reimprimer($courrier);
        } catch (\Throwable $e) {
            $io->error("L'impression du courrier de décision a échoué : {$e->getMessage()}");

            return Command::FAILURE;
        }

        $io->success(sprintf(
            'Courrier de décision du dossier %s (id %d) réimprimé : %s → %s',
            $dossier->getReference(),
            $dossier->getId(),
            $ancienFichier ?? '(aucun)',
            $courrier->getFilename()
        ));

        return Command::SUCCESS;
    }
}
