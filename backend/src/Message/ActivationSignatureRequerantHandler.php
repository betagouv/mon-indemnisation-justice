<?php

namespace MonIndemnisationJustice\Message;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\RequeteSignatureRequerant;
use MonIndemnisationJustice\Service\DiffuseurDossierRequerant;
use MonIndemnisationJustice\Service\Signature\GestionnaireSignature;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class ActivationSignatureRequerantHandler
{
    public function __construct(
        protected readonly GestionnaireSignature $gestionnaireSignature,
        protected readonly DiffuseurDossierRequerant $diffuseurDossierRequerant,
        protected readonly EntityManagerInterface $em,
        protected readonly LoggerInterface $logger,
    ) {

    }

    public function __invoke(ActivationSignatureRequerant $activation): void
    {
        $requeteSignature = $this->em->getRepository(RequeteSignatureRequerant::class)->find($activation->id);

        if (null === $requeteSignature) {
            $this->logger->warning('Aucune requête de signature à activer', [
                'id' => $activation->id,
            ]);

            return;
        }

        $requeteSignature = $this->gestionnaireSignature->activerSignatureRequerant($requeteSignature, $activation->contexte->rib);
        $this->em->persist($requeteSignature);
        $this->em->flush();

        // La synchronisation est déjà enregistrée : une indisponibilité du hub Mercure ne doit pas la remettre en cause
        try {
            $this->diffuseurDossierRequerant->diffuserDossier($requeteSignature->getDossier());
        } catch (\Throwable $exception) {
            $this->logger->warning("Impossible de diffuser le dossier de la requête de signature {$requeteSignature->getId()}", [
                'erreur' => $exception->getMessage(),
            ]);
        }
    }
}
