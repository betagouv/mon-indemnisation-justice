<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Command;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Service\ImprimanteCourrier;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'mij:dossier:imprimer', description: "Imprimer le courrier d'un dossier")]
class ImprimerDossierCourrierCommand extends Command
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ImprimanteCourrier $imprimante,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('id', InputArgument::REQUIRED)
            ->addOption('garder-fichiers', 'g', InputOption::VALUE_NONE, description: 'Garder les fichiers temporaires')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id = $input->getArgument('id');
        $garderFichiers = $input->getOption('garder-fichiers');

        /* @var BrisPorte $brisPorte */
        $brisPorte = $this->em->find(BrisPorte::class, $id);

        if (null === $brisPorte) {
            throw new \LogicException("Aucun bris de porte d'id $id");
        }

        $path = $this->imprimante->imprimerCourrier($brisPorte);

        $output->writeln($path);

        return Command::SUCCESS;
    }
}
