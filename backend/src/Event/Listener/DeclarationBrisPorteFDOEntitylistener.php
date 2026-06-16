<?php

namespace MonIndemnisationJustice\Event\Listener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use MonIndemnisationJustice\Service\GenerateurReferenceCourte;
use MonIndemnisationJustice\Service\Mailer;

#[AsEntityListener(DeclarationFDOBrisPorte::class)]
class DeclarationBrisPorteFDOEntitylistener
{
    public function __construct(
        protected readonly GenerateurReferenceCourte $generateurReferenceCourte,
        protected readonly Mailer $mailer,
    ) {
    }

    public function prePersist(DeclarationFDOBrisPorte $declaration, PrePersistEventArgs $args)
    {
        // Génération de la référence courte de dossier
        $repository = $args->getObjectManager()->getRepository(DeclarationFDOBrisPorte::class);

        $declaration->setReference($this->generateurReferenceCourte->genererJusque(fn ($reference) => null === $repository->findOneBy(['reference' => $reference]), nbTentatives: 5));
        $declaration->setDateSoumission(new \DateTimeImmutable());

        // Envoi du mail d'invitation à déclarer
        if (null !== ($coordonneesRequerant = $declaration->getCoordonneesRequerant())) {
            $this->mailer
                ->to($coordonneesRequerant->getCourriel(), $coordonneesRequerant->getPrenom().' '.$coordonneesRequerant->getNom())
                ->subject("Mon Indemnisation Justice: vous pouvez faire une demande d'indemnisation")
                ->htmlTemplate('email/invitation_a_deposer.html.twig', [
                    'declaration' => $declaration,
                ])
                ->send();
        }
    }
}
