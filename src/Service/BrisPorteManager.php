<?php

namespace App\Service;

use App\Event\BrisPorteConstitueEvent;
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
            BrisPorteConstitueEvent::class => 'onDossierConstitue',
        ];
    }

    public function onDossierConstitue(BrisPorteConstitueEvent $event): void
    {
        $this->mailer
            ->to($this->courrielEquipe)
            ->subject("Mon indemnisation justice: nouveau dossier d'indemnisation de bris de porte déposé")
            ->htmlTemplate('email/agent_nouveau_dossier_constitue.html.twig', [
                'brisPorte' => $event->brisPorte,
            ]);
        foreach ($event->brisPorte->getLiasseDocumentaire()->getDocuments() as $document) {
            $this->mailer->addAttachment(
                $this->documentManager->getDocumentBody($document),
                $document
            );
        }

        $this->mailer->send();
    }
}
