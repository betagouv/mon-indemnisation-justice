<?php

namespace MonIndemnisationJustice\Event\Listener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostFlushEventArgs;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Events;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use MonIndemnisationJustice\Entity\EnvoiCourrielType;
use MonIndemnisationJustice\Service\GenerateurCodeInvitation;
use MonIndemnisationJustice\Service\Mailer;

#[AsEntityListener(DeclarationFDOBrisPorte::class)]
#[AsDoctrineListener(event: Events::postFlush)]
class DeclarationBrisPorteFDOEntitylistener
{
    /**
     * Déclarations pour lesquelles faire partir le courriel d'invitation une fois flushées.
     *
     * @var array<string, DeclarationFDOBrisPorte>
     */
    protected array $invitationsAEnvoyer = [];

    /**
     * Vrai pendant l'envoi des invitations dans `postFlush`, y compris pendant le flush qui enregistre les `EnvoiCourriel`
     * créés : ce flush ne concerne pas les déclarations, et le listener doit donc l'ignorer.
     *
     * C'est d'autant plus nécessaire que Doctrine déclenche `postFlush` *avant* de purger les changesets de l'UnitOfWork :
     * sans ce drapeau, `preFlush` verrait encore l'adresse courriel comme modifiée et reprogrammerait l'invitation.
     */
    protected bool $envoiEnCours = false;

    public function __construct(
        protected readonly GenerateurCodeInvitation $generateurCodeInvitation,
        protected readonly Mailer $mailer,
    ) {
    }

    /**
     * Si la déclaration est soumise, alors on génère la référence et on programme l'envoi du courriel d'invitation.
     *
     * Si la déclaration était déjà soumise et que le courriel du requérant a changé, le courriel d'invitation doit également
     * être renvoyé.
     *
     * Le courriel n'est pas envoyé ici, mais dans `postFlush`, une fois les modifications effectivement écrites en base.
     */
    public function preFlush(DeclarationFDOBrisPorte $declaration, PreFlushEventArgs $args): void
    {
        // Si la déclaration est en brouillon, ou si on est en train d'envoyer les invitations, alors rien à faire
        if ($this->envoiEnCours || $declaration->estBrouillon()) {
            return;
        }

        // Déclaration déjà soumise : on renvoie l'invitation uniquement si l'adresse courriel est définie et a changé
        if (null !== $declaration->getReference()) {
            $coordonnees = $declaration->getCoordonneesRequerant();
            if (!empty($nouvelleAdresseCourriel = $coordonnees?->getCourriel())) {
                $unitOfWork = $args->getObjectManager()->getUnitOfWork();
                $estAdresseCourrielModifiee =
                    // Coordonnées nouvellement créées...
                    !$unitOfWork->isInIdentityMap($coordonnees) || $unitOfWork->isScheduledForInsert($coordonnees)
                    // ... ou changeset déjà calculé...
                    || isset($unitOfWork->getEntityChangeSet($coordonnees)['courriel'])
                    // ... ou sinon comparaison avec les données d'origine
                    || ($unitOfWork->getOriginalEntityData($coordonnees)['courriel'] ?? null) !== $nouvelleAdresseCourriel;

                if ($estAdresseCourrielModifiee) {
                    $this->invitationsAEnvoyer[$declaration->getId()->toRfc4122()] = $declaration;
                }
            }
        } else {
            // Génération du code d'invitation, unique parmi les déclarations existantes
            $repository = $args->getObjectManager()->getRepository(DeclarationFDOBrisPorte::class);
            do {
                $reference = $this->generateurCodeInvitation->generer();
            } while (null !== $repository->findOneBy(['reference' => $reference]));
            $declaration->setReference($reference);

            $this->invitationsAEnvoyer[$declaration->getId()->toRfc4122()] = $declaration;
        }
    }

    /**
     * Envoie les courriels d'invitation programmés, maintenant que les modifications sont écrites en base.
     */
    public function postFlush(PostFlushEventArgs $args): void
    {
        if ($this->envoiEnCours || empty($this->invitationsAEnvoyer)) {
            return;
        }

        $declarations = $this->invitationsAEnvoyer;
        $this->invitationsAEnvoyer = [];

        $this->envoiEnCours = true;
        try {
            foreach ($declarations as $declaration) {
                $this->envoyerCourrielInvitation($declaration);
            }

            // Un seul flush pour enregistrer l'ensemble des `EnvoiCourriel` créés
            $args->getObjectManager()->flush();
        } finally {
            $this->envoiEnCours = false;
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
            ->send(
                EnvoiCourrielType::INVITATION_FDO,
                $declaration,
                flush: false,
            );
    }
}
