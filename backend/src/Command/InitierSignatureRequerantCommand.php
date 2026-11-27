<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Command;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\DossierType;
use MonIndemnisationJustice\Service\Signature\GestionnaireSignature;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'mij:initier:signature:requerant', description: 'Initier la signature électronique par le requérant')]
class InitierSignatureRequerantCommand extends Command
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly GestionnaireSignature $gestionnaireSignature,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('id', InputArgument::REQUIRED, 'Id ou référence du dossier');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id = $input->getArgument('id');
        $dossier = $this->em->getRepository(Dossier::class)->getByIdOuReference($id, DossierType::BRIS_PORTE);

        if (null == $dossier) {
            throw new \LogicException("Aucun dossier trouve pour l'id {$id}");
        }

        $requeteSignature = $this->gestionnaireSignature->initierSignatureRequerant($dossier);

        $output->writeln("<info>Requête de signature {$requeteSignature->getId()} ({$requeteSignature->getUrlSignatureExterne()}) initiée</info>");

        return Command::SUCCESS;
    }
}
