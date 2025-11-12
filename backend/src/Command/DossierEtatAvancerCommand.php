<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Command;

use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Repository\AgentRepository;
use MonIndemnisationJustice\Repository\BrisPorteRepository;
use MonIndemnisationJustice\Service\DocumentManager;
use MonIndemnisationJustice\Service\DossierManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'mij:dossier:avancer', description: "Faire avancer un dossier à l'état suivant")]
class DossierEtatAvancerCommand extends Command
{
    public function __construct(
        protected readonly BrisPorteRepository $dossierRepository,
        protected readonly DossierManager $dossierManager,
        protected readonly AgentRepository $agentRepository,
        protected readonly DocumentManager $documentManager,
    ) {
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addArgument('id', InputArgument::REQUIRED)
            ->addOption('contexte', 'c', InputOption::VALUE_REQUIRED, 'Le contexte, en JSON')
            ->addOption('fichier', 'f', InputOption::VALUE_REQUIRED, "Le chemin ou l'URL vers le fichier à ajouter")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id = $input->getArgument('id');
        $dossier = $this->dossierRepository->getByIdOuReference($id);

        if (null == $dossier) {
            throw new \LogicException("Aucun dossier trouve pour l'id {$id}");
        }

        $contexte = $input->getOption('contexte') ? json_decode($input->getOption('contexte'), true) : null;
        $fichier = $input->getOption('fichier');
        if (null !== $fichier) {
            $type = $this->getTypeFichierPourAction($dossier);
            if (null === $type) {
                throw new \LogicException('Impossible de déterminer le type de fichier attendu à ce stade du dossier');
            }

            $this->documentManager->ajouterFichierLocal($dossier, $fichier, $type, EtatDossierType::DOSSIER_OK_A_VERIFIER === $dossier->getEtatDossier()->getEtat());
        }

        $this->dossierManager->avancer($dossier, $this->getAgentPourAction($dossier), $contexte);

        return Command::SUCCESS;
    }

    /**
     * Détermine l'agent qui fait l'action en fonction de l'état initial du dossier.
     */
    protected function getAgentPourAction(BrisPorte $dossier): ?Agent
    {
        return match ($dossier->getEtatDossier()->getEtat()) {
            EtatDossierType::DOSSIER_A_INSTRUIRE, EtatDossierType::DOSSIER_EN_INSTRUCTION, EtatDossierType::DOSSIER_OK_A_VERIFIER => $dossier->getRedacteur(),
            EtatDossierType::DOSSIER_OK_A_SIGNER, EtatDossierType::DOSSIER_KO_A_SIGNER, EtatDossierType::DOSSIER_OK_VERIFIE => $this->agentRepository->getValidateur(),
            EtatDossierType::DOSSIER_A_ATTRIBUER => $this->agentRepository->getAtributeur(),
            EtatDossierType::DOSSIER_OK_A_INDEMNISER, EtatDossierType::DOSSIER_OK_EN_ATTENTE_PAIEMENT => $this->agentRepository->getAgentLiaison(),
            default => null
        };
    }

    /**
     * Détermine le type de fichier attendu en fonction de l'état initial du dossier.
     *
     * @return null|Agent
     */
    protected function getTypeFichierPourAction(BrisPorte $dossier): ?DocumentType
    {
        return match ($dossier->getEtatDossier()->getEtat()) {
            EtatDossierType::DOSSIER_OK_A_SIGNER, EtatDossierType::DOSSIER_KO_A_SIGNER => DocumentType::TYPE_COURRIER_MINISTERE,
            EtatDossierType::DOSSIER_OK_A_APPROUVER => DocumentType::TYPE_COURRIER_REQUERANT,
            EtatDossierType::DOSSIER_OK_A_VERIFIER => DocumentType::TYPE_ARRETE_PAIEMENT,
            default => null
        };
    }
}
