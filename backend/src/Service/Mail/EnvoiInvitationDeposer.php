<?php

namespace MonIndemnisationJustice\Service\Mail;

use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use MonIndemnisationJustice\Service\Mailer;

/**
 * Envoie au requérant le mail l'invitant à déposer sa demande d'indemnisation.
 */
class EnvoiInvitationDeposer
{
    public function __construct(
        protected readonly Mailer $mailer,
    ) {
    }

    public function envoyer(DeclarationFDOBrisPorte $declaration): void
    {
        $coordonneesRequerant = $declaration->getCoordonneesRequerant();

        if (null === $coordonneesRequerant) {
            return;
        }

        $this->mailer
            ->to($coordonneesRequerant->getCourriel(), $coordonneesRequerant->getPrenom().' '.$coordonneesRequerant->getNom())
            ->subject("Mon Indemnisation Justice: vous pouvez faire une demande d'indemnisation")
            ->htmlTemplate('email/invitation_a_deposer.html.twig', [
                'declaration' => $declaration,
            ])
            ->send();
    }
}
