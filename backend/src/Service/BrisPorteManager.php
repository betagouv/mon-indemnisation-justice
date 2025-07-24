<?php

namespace MonIndemnisationJustice\Service;

use MonIndemnisationJustice\Event\DossierConstitueEvent;
use MonIndemnisationJustice\Event\DossierDecideEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BrisPorteManager implements EventSubscriberInterface
{
    public function __construct(
        protected readonly Mailer $mailer,
        protected readonly DocumentManager $documentManager,
        protected readonly string $courrielEquipe,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            DossierConstitueEvent::class => 'onDossierConstitue',
            DossierDecideEvent::class => 'onDossierDecide',
        ];
    }

    public function onDossierConstitue(DossierConstitueEvent $event): void
    {
        $this->mailer
            ->to($this->courrielEquipe)
            ->subject("Mon Indemnisation Justice: nouveau dossier d'indemnisation de bris de porte déposé")
            ->htmlTemplate('email/agent_nouveau_dossier_constitue.html.twig', [
                'dossier' => $event->brisPorte,
            ]);

        $this->mailer->send();
    }

    public function onDossierDecide(DossierDecideEvent $event): void
    {
        $this->mailer
            ->to($event->dossier->getRequerant()->getEmail(), $event->dossier->getRequerant()->getNomCourant())
            ->subject("Mon Indemnisation Justice: votre demande d'indemnisation a obtenu une réponse")
            ->htmlTemplate('email/requerant_dossier_decide.twig', [
                'dossier' => $event->dossier,
            ]);

        $this->mailer->send();
    }
}
