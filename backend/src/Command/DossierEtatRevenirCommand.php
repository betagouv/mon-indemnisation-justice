<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Command;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\EtatDossier;
use MonIndemnisationJustice\Service\DossierManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

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
        $this->addArgument('id', InputArgument::REQUIRED, 'Id ou référence du dossier');
        $this->addArgument('nb-etapes', InputArgument::OPTIONAL, "Nombre d'étapes où revenir en arrière", 1);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id = $input->getArgument('id');
        $nbEtapes = intval($input->getArgument('nb-etapes'));

        $dossier = $this->em->getRepository(BrisPorte::class)->getByIdOuReference($id);

        if (null == $dossier) {
            throw new \LogicException("Aucun dossier trouve pour l'id {$id}");
        }

        /** @var EtatDossier $etatReference */
        $etatReference = $dossier->getHistoriqueEtats()->get($dossier->getHistoriqueEtats()->count() - ($nbEtapes + 1));

        if ($etatReference->getEtat() === $dossier->getEtatDossier()->getEtat()) {
            $helper = new QuestionHelper();
            // Avertir que la manipulation risque d'être vaine
            $question = new ConfirmationQuestion("<comment>L'état cible est le même que l'état actuel, continuer ?</comment>", false);

            if (!$helper->ask($input, $output, $question)) {
                return Command::SUCCESS;
            }
        }

        $this->dossierManager->revenir($dossier, $nbEtapes);

        return Command::SUCCESS;
    }
}
