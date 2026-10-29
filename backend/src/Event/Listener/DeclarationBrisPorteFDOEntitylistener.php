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
     * Appelé avant le calcul du changeset : contrairement à `preUpdate`, les modifications apportées ici à la déclaration
     * sont bien persistées, que la déclaration soit nouvelle ou déjà en base.
     */
    public function preFlush(DeclarationFDOBrisPorte $declaration, PreFlushEventArgs $args)
    {
        // Un brouillon n'est jamais envoyé : le code d'invitation n'est généré qu'à la soumission, et une seule fois
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
