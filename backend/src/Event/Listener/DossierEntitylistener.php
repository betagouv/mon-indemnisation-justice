<?php

namespace MonIndemnisationJustice\Event\Listener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use MonIndemnisationJustice\Entity\BrisPorte;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsEntityListener]
class DossierEntitylistener
{
    public function __construct(
        protected readonly EventDispatcherInterface $eventDispatcher
    ) {}

    public function preUpdate(BrisPorte $dossier, PreUpdateEventArgs $args)
    {
        if (isset($args->getEntityChangeSet()['etatDossier'])) {
            $evenement = $dossier->getEtatDossier()->getEtat()->creerTransitionEvent($dossier);

            if (null !== $evenement) {
                $this->eventDispatcher->dispatch($evenement);
            }
        }
    }
}
