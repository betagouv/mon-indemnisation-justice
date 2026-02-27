<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Command;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\Dossier;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'mij:dossier:supprimer', description: 'Supprimer requérant')]
class SupprimerDossierCommand extends Command
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->addArgument('id', InputArgument::REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id = $input->getArgument('id');
        $dossier = $this->em->find(Dossier::class, $id);

        if (null == $dossier) {
            throw new \LogicException("Aucun dossier trouve pour l'id $id");
        }

        $this->em->remove($dossier);
        $this->em->flush();

        return Command::SUCCESS;
    }
}
