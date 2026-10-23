<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Command;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\DossierType;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Service\Signature\GestionnaireSignatureRequerantInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'mij:signature:requerant:initier', description: 'Initier la signature électronique par le requérant')]
class InitierSignatureRequerantCommand extends Command
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly GestionnaireSignatureRequerantInterface $gestionnaireSignatureRequerant,
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

        if (EtatDossierType::DOSSIER_OK_A_APPROUVER !== $dossier->getEtatDossier()->getEtat()) {
            throw new \LogicException("Ce dossier n'est pas à accepter par le requérant");
        }
        $this->gestionnaireSignatureRequerant->creerRequeteSignature($dossier);

        return Command::SUCCESS;
    }
}
