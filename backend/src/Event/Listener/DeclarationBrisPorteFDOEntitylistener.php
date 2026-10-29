<?php

namespace MonIndemnisationJustice\Event\Listener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreFlushEventArgs;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use MonIndemnisationJustice\Service\GenerateurCodeInvitation;
use MonIndemnisationJustice\Service\Mailer;

#[AsEntityListener(DeclarationFDOBrisPorte::class)]
class DeclarationBrisPorteFDOEntitylistener
{
    public function __construct(
        protected readonly GenerateurCodeInvitation $generateurCodeInvitation,
        protected readonly Mailer $mailer,
    ) {
    }

    /**
     * Si la déclaration est soumise, alors on génère la référence et on fait partir le courriel d'invitation.
     *
     * Si la déclaration était déjà soumise et que le courriel du requérant a changé, le courriel d'invitation doit également
     * être renvoyé.
     */
    public function preFlush(DeclarationFDOBrisPorte $declaration, PreFlushEventArgs $args)
    {
        // Si la déclaration est en brouillon alors rien à faire
        if ($declaration->estBrouillon()) {
            return;
        }

        // Déclaration déjà soumise : on renvoie l'invitation uniquement si l'adresse courriel est définie et a changé
        if (null !== $declaration->getReference()) {
            $coordonnees = $declaration->getCoordonneesRequerant();
            if (!empty($nouvelleAdresseCourriel = $coordonnees?->getCourriel())) {
                $unitOfWork = $args->getObjectManager()->getUnitOfWork();
                $courrielModifie =
                    // Coordonnées nouvellement créées ...
                    !$unitOfWork->isInIdentityMap($coordonnees) || $unitOfWork->isScheduledForInsert($coordonnees)
                    // ... ou changeset déjà calculé ...
                    || isset($unitOfWork->getEntityChangeSet($coordonnees)['courriel'])
                    // ... ou sinon comparaison avec les données d'origine
                    || ($unitOfWork->getOriginalEntityData($coordonnees)['courriel'] ?? null) !== $nouvelleAdresseCourriel;

                if ($courrielModifie) {
                    // Envoi du courriel d'invitation à déclarer
                    $this->envoyerCourrielInvitation($declaration);
                }
            }
        } else {
            // Génération du code d'invitation, unique parmi les déclarations existantes
            $repository = $args->getObjectManager()->getRepository(DeclarationFDOBrisPorte::class);
            do {
                $reference = $this->generateurCodeInvitation->generer();
            } while (null !== $repository->findOneBy(['reference' => $reference]));
            $declaration->setReference($reference);

            // Envoi du courriel d'invitation à déclarer
            $this->envoyerCourrielInvitation($declaration);
        }
    }

    protected function envoyerCourrielInvitation(DeclarationFDOBrisPorte $declaration): void
    {
        if (null === $declaration->getCoordonneesRequerant()) {
            return;
        }

        $this->mailer
            ->to($declaration->getCoordonneesRequerant()->getCourriel(), $declaration->getCoordonneesRequerant()->getPrenom().' '.$declaration->getCoordonneesRequerant()->getNom())
            ->subject("Mon Indemnisation Justice: vous pouvez faire une demande d'indemnisation")
            ->htmlTemplate('email/invitation_a_deposer.html.twig', [
                'declaration' => $declaration,
            ])
            ->send();
    }
}
