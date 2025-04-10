<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Command;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\BrisPorte;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'mij:dossier:etat_revenir', description: "Faire revenir un dossier à l'état précédent")]
class DossierEtatRevenirCommand extends Command
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
        $dossier = $this->em->find(BrisPorte::class, $id);

        if (null == $dossier) {
            throw new \LogicException("Aucun dossier trouve pour l'id $id");
        }

        $etat = $dossier->getEtatDossier();

        $dossier->revenirEtatPrecedent();
        $this->em->remove($etat);
        $this->em->flush();

        return Command::SUCCESS;
    }
}
