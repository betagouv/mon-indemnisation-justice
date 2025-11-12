<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Command;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Service\DossierManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'mij:dossier:revenir_etat', description: "Faire revenir un dossier à l'état précédent")]
class DossierEtatRevenirCommand extends Command
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly DossierManager $dossierManager,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this->addArgument('id', InputArgument::REQUIRED);
        $this->addArgument('nb-etapes', InputArgument::OPTIONAL, "Nombre d'étapes où revenir en arrière", 1);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id = $input->getArgument('id');
        $nbEtapes = intval($input->getArgument('nb-etapes'));
        $dossier = $this->em->find(BrisPorte::class, $id);

        if (null == $dossier) {
            throw new \LogicException("Aucun dossier trouve pour l'id {$id}");
        }

        $this->dossierManager->revenir($dossier, $nbEtapes);

        return Command::SUCCESS;
    }
}
