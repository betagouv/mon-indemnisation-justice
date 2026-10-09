<?php

namespace MonIndemnisationJustice\Event\Listener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
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

    public function prePersist(DeclarationFDOBrisPorte $declaration, PrePersistEventArgs $args)
    {
        // Génération du code d'invitation, unique parmi les déclarations existantes
        $repository = $args->getObjectManager()->getRepository(DeclarationFDOBrisPorte::class);

        do {
            $reference = $this->generateurCodeInvitation->generer();
        } while (null !== $repository->findOneBy(['reference' => $reference]));

        $declaration->setReference($reference);
        $declaration->setDateSoumission(new \DateTimeImmutable());

        // Envoi du mail d'invitation à déclarer
        $this->envoiInvitationDeposer->envoyer($declaration);
    }
}
