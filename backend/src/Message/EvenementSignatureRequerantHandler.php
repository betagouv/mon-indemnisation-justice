<?php

namespace MonIndemnisationJustice\Message;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\RequeteSignatureRequerant;
use MonIndemnisationJustice\Service\Signature\GestionnaireSignature;
use MonIndemnisationJustice\Service\Signature\DiffuseurSignatureRequerant;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class EvenementSignatureRequerantHandler
{
    public function __construct(
        protected readonly GestionnaireSignature $gestionnaireSignature,
        protected readonly DiffuseurSignatureRequerant $diffuseurSignatureRequerant,
        protected readonly EntityManagerInterface $em,
        protected readonly LoggerInterface $logger,
    ) {

    }

    public function __invoke(EvenementSignatureRequerant $evenement)
    {
        try {
            $requeteSignature = $this->gestionnaireSignature->synchroniserEvenement($evenement->donnees);
        } catch (\Throwable $exception) {
            $this->logger->error("Erreur lors du traitement du webhook d'évènement signature requérant", [
                'erreur' => $exception->getMessage(),
                'donnees' => $evenement->donnees,
            ]);

            return;
        }

        if ($requeteSignature instanceof RequeteSignatureRequerant) {
            $this->logger->info("Requête de signature {$requeteSignature->getId()} synchronisée");

            // La synchronisation est déjà enregistrée : une indisponibilité du hub Mercure ne doit pas la remettre en cause
            try {
                $this->diffuseurSignatureRequerant->diffuserSignature($requeteSignature);
            } catch (\Throwable $exception) {
                $this->logger->warning("Impossible de diffuser la signature de la requête {$requeteSignature->getId()}", [
                    'erreur' => $exception->getMessage(),
                ]);
            }
        }
    }
}
