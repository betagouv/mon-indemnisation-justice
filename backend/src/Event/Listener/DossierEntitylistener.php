<?php

namespace MonIndemnisationJustice\Event\Listener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Repository\BrisPorteRepository;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

#[AsEntityListener(BrisPorte::class)]
class DossierEntitylistener
{
    public function __construct(
        protected readonly EventDispatcherInterface $eventDispatcher,
        protected readonly BrisPorteRepository $brisPorteRepository,
        #[Autowire(param: 'kernel.runtime_mode.web')]
        protected readonly bool $estContexteWeb,
    ) {}

    public function preUpdate(BrisPorte $dossier, PreUpdateEventArgs $args)
    {
        if ($this->estContexteWeb && $args->hasChangedField('etatDossier')) {
            if (
                EtatDossierType::DOSSIER_A_ATTRIBUER === $args->getNewValue('etatDossier')->getEtat()
                && null === $dossier->getReference()
            ) {
                $dossier->setReference($this->brisPorteRepository->calculerReference($dossier));
            }

            $evenement = $dossier->getEtatDossier()->getEtat()->creerTransitionEvent($dossier);

            if (null !== $evenement) {
                $this->eventDispatcher->dispatch($evenement);
            }
        }
    }
}
