<?php

namespace MonIndemnisationJustice\Service;

use MonIndemnisationJustice\Event\DossierConstitueEvent;
use MonIndemnisationJustice\Event\DossierDecideEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class BrisPorteManager implements EventSubscriberInterface
{
    public function __construct(
        protected readonly Mailer $mailer,
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
        // TODO prévenir l'agent attributeur qu'un dossier a été constitué et qu'il doit être attribué
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
