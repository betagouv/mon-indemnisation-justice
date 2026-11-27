<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Command;

use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\DossierType;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Repository\DossierRepository;
use MonIndemnisationJustice\Service\DocumentManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'mij:imprimer:courrier', description: "Réimprimer en PDF un courrier d'un dossier (décision, déclaration d'acceptation ou arrêté de paiement)")]
class ImprimerCourrierCommand extends Command
{
    protected const TYPE_DECISION = 'decision';
    protected const TYPE_ACCEPTATION = 'acceptation';
    protected const TYPE_ARRETE = 'arrete';

    protected const TYPES = [
        self::TYPE_DECISION,
        self::TYPE_ACCEPTATION,
        self::TYPE_ARRETE,
    ];

    public function __construct(
        protected readonly DossierRepository $dossierRepository,
        protected readonly DocumentManager $documentManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('type', InputArgument::REQUIRED, sprintf('Type de courrier à réimprimer (%s)', implode('|', self::TYPES)), null, self::TYPES)
            ->addArgument('dossier', InputArgument::REQUIRED, 'Identifiant ou référence du dossier');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $type = $input->getArgument('type');
        $idOuReference = $input->getArgument('dossier');

        if (!in_array($type, self::TYPES, true)) {
            $io->error(sprintf("Type de courrier '%s' inconnu, valeurs possibles : %s", $type, implode('|', self::TYPES)));

            return Command::INVALID;
        }

        $dossier = $this->dossierRepository->getByIdOuReference($idOuReference, DossierType::BRIS_PORTE);

        if (null === $dossier) {
            $io->error("Aucun dossier trouvé pour l'identifiant ou la référence '{$idOuReference}'");

            return Command::FAILURE;
        }

        try {
            $document = match ($type) {
                self::TYPE_DECISION => $this->traiterDecision($dossier),
                self::TYPE_ACCEPTATION => $this->traiterAcceptation($dossier),
                self::TYPE_ARRETE => $this->traiterArrete($dossier),
            };
        } catch (\DomainException $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        } catch (\Throwable $e) {
            $io->error("L'impression du courrier a échoué : {$e->getMessage()}");

            return Command::FAILURE;
        }

        $io->success(sprintf(
            'Courrier "%s" du dossier %s (id %d) réimprimé : %s',
            $type,
            $dossier->getReference(),
            $dossier->getId(),
            $document->getFilename()
        ));

        return Command::SUCCESS;
    }

    /**
     * Le courrier de décision peut être réimprimé tant qu'il n'a pas été signé par l'agent validateur.
     */
    protected function traiterDecision(Dossier $dossier): Document
    {
        if (!$dossier->estASigner()) {
            throw new \DomainException("Le dossier {$dossier->getReference()} n'est pas en attente de signature de la décision");
        }

        return $this->reimprimer(
            $dossier->getCourrierDecision(),
            "Le dossier {$dossier->getReference()} n'a pas de courrier de décision"
        );
    }

    /**
     * La déclaration d'acceptation peut être réimprimée tant que le requérant ne l'a pas signée, et qu'aucune requête
     * de signature n'est en cours (le document envoyé au prestataire de signature ne correspondrait plus).
     */
    protected function traiterAcceptation(Dossier $dossier): Document
    {
        if (!in_array($dossier->getEtatDossier()->getEtat(), [EtatDossierType::DOSSIER_OK_A_SIGNER, EtatDossierType::DOSSIER_OK_A_APPROUVER], true)) {
            throw new \DomainException("Le dossier {$dossier->getReference()} n'est pas en attente d'acceptation par le requérant");
        }

        // Réimpression possible tant qu'aucune signature n'est en cours (pas de requête, ou requête fermée)
        if (!$dossier->peutDemarrerSignature()) {
            throw new \DomainException("Une requête de signature est en cours pour le dossier {$dossier->getReference()}");
        }

        return $this->reimprimer(
            $dossier->getDocumentParType(DocumentType::TYPE_FORMULAIRE_ACCEPTATION),
            "Le dossier {$dossier->getReference()} n'a pas de déclaration d'acceptation"
        );
    }

    /**
     * L'arrêté de paiement peut être réimprimé une fois l'indemnisation acceptée par le requérant, tant qu'il n'a pas
     * été signé par l'agent validateur.
     */
    protected function traiterArrete(Dossier $dossier): Document
    {
        if (!in_array($dossier->getEtatDossier()->getEtat(), [EtatDossierType::DOSSIER_OK_A_VERIFIER, EtatDossierType::DOSSIER_OK_VERIFIE], true)) {
            throw new \DomainException("Le dossier {$dossier->getReference()} n'est pas en attente de signature de l'arrêté de paiement");
        }

        return $this->reimprimer(
            $dossier->getArretePaiement(),
            "Le dossier {$dossier->getReference()} n'a pas d'arrêté de paiement"
        );
    }

    protected function reimprimer(?Document $document, string $messageAbsent): Document
    {
        if (null === $document) {
            throw new \DomainException($messageAbsent);
        }

        return $this->documentManager->reimprimer($document);
    }
}
