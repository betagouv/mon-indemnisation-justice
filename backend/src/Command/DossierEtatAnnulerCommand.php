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

#[AsCommand(name: 'mij:dossier:annuler', description: "Annuler l'état actuel d'un dossier restaurer l'état précédent")]
class DossierEtatAnnulerCommand extends Command
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
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id = $input->getArgument('id');
        $dossier = $this->em->getRepository(BrisPorte::class)->getByIdOuReference($id);

        if (null == $dossier) {
            throw new \LogicException("Aucun dossier trouve pour l'id {$id}");
        }

        $this->dossierManager->annuler($dossier);

        return Command::SUCCESS;
    }
}
