<?php

namespace MonIndemnisationJustice\Event;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Si la variable d'environnement MIJ_DOMAINE_PRIMAIRE est définie et que le domaine actuellement demandé ne correspond
 * pas, on redirige vers le domaine canonique.
 */
class RedirectionDomainePrimaireListener implements EventSubscriberInterface
{
    public function __construct(
        #[Autowire(env: 'default::MIJ_DOMAINE_PRIMAIRE')]
        protected readonly ?string $domainePrimaire,
        protected readonly LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 1],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!empty($this->domainePrimaire)) {
            if ($event->getRequest()->getHost() !== $this->domainePrimaire) {
                $this->logger->warning("Redirection depuis {$event->getRequest()->getUri()}");
                $event->setResponse(new RedirectResponse('https://'.$this->domainePrimaire.$event->getRequest()->getRequestUri(), 301));
            }
        }
    }
}
