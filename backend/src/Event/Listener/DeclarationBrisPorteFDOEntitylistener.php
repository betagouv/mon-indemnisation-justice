<?php

namespace MonIndemnisationJustice\Event\Listener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreFlushEventArgs;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use MonIndemnisationJustice\Service\GenerateurCodeInvitation;
use MonIndemnisationJustice\Service\Mail\EnvoiInvitationDeposer;

#[AsEntityListener(DeclarationFDOBrisPorte::class)]
class DeclarationBrisPorteFDOEntitylistener
{
    public function __construct(
        protected readonly GenerateurCodeInvitation $generateurCodeInvitation,
        protected readonly EnvoiInvitationDeposer $envoiInvitationDeposer,
    ) {
    }

    /**
     * Si la déclaration est soumise, alors on génère la référence et on fait partir l'email d'invitation.
     */
    public function preFlush(DeclarationFDOBrisPorte $declaration, PreFlushEventArgs $args)
    {
        // Si la déclaration est en brouillon ou a déjà une référence alors rien à faire
        if ($declaration->estBrouillon() || null !== $declaration->getReference()) {
            return;
        }

        // Génération du code d'invitation, unique parmi les déclarations existantes
        $repository = $args->getObjectManager()->getRepository(DeclarationFDOBrisPorte::class);
        do {
            $reference = $this->generateurCodeInvitation->generer();
        } while (null !== $repository->findOneBy(['reference' => $reference]));
        $declaration->setReference($reference);

        // Envoi du mail d'invitation à déclarer
        $this->envoiInvitationDeposer->envoyer($declaration);
    }
}
