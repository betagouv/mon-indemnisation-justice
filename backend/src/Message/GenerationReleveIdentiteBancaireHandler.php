<?php

namespace MonIndemnisationJustice\Message;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\ReferenceBancaire;
use MonIndemnisationJustice\Service\Banque\GenerateurReleveIdentiteBancaire;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class GenerationReleveIdentiteBancaireHandler
{
    public function __construct(
        protected readonly GenerateurReleveIdentiteBancaire $generateur,
        protected readonly EntityManagerInterface $em,
        protected readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(GenerationReleveIdentiteBancaire $generation): void
    {
        $referenceBancaire = $this->em->find(ReferenceBancaire::class, $generation->idReferenceBancaire);

        if (null === $referenceBancaire) {
            $this->logger->warning("Aucune référence bancaire pour générer le relevé d'identité bancaire", [
                'id' => $generation->idReferenceBancaire,
            ]);

            return;
        }

        // En cas d'échec, l'exception remonte : le message est rejoué selon la stratégie du transport
        $document = $this->generateur->generer(
            $referenceBancaire,
            null !== $generation->idDossier ? $this->em->find(Dossier::class, $generation->idDossier) : null
        );

        $this->logger->info("Relevé d'identité bancaire généré", [
            'referenceBancaire' => $referenceBancaire->getId(),
            'document' => $document->getId(),
        ]);
    }
}
