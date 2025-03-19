<?php

namespace MonIndemnisationJustice\Event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class DomaineRedirecteurListener implements EventSubscriberInterface
{
    public function __construct(
        protected readonly ?string $domainePrimaire = null,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 2],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        /*
        if (null !== $this->domainePrimaire) {

            if ($event->getRequest()->getHost() !== $this->domainePrimaire) {
                $event->setResponse(new RedirectResponse(str_replace($event->getRequest()->getHost(), $this->domainePrimaire, $event->getRequest()->getUri())));
            }
        }
        */
    }
}
