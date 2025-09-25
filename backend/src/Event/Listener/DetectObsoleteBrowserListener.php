<?php

namespace MonIndemnisationJustice\Event\Listener;

use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Device\AbstractDeviceParser;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

/**
 * Détecte la version du navigateur et redirige vers une page d'avertissement de non-prise en charge en cas de version
 * obsolète.
 *
 * La version du navigateur doit être supérieure ou égale aux versions:
 * - supportées par le DSFR https://www.systeme-de-design.gouv.fr/a-propos/configuration-minimale-requise-pour-utiliser-le-dsfr/
 * - prenant en charge les ESM https://caniuse.com/?search=ESM
 */
class DetectObsoleteBrowserListener implements EventSubscriberInterface
{
    public function __construct(
        protected readonly Environment $twig,
        protected readonly DeviceDetector $device,
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
        $this->device->parse();

        if (
            in_array($this->device->getDevice(), [AbstractDeviceParser::DEVICE_TYPE_DESKTOP, AbstractDeviceParser::DEVICE_TYPE_SMARTPHONE, AbstractDeviceParser::DEVICE_TYPE_TABLET, AbstractDeviceParser::DEVICE_TYPE_PHABLET])
            && $this->device->isBrowser()
        ) {
            $browser = $this->device->getClient()['name'];
            $version = self::extractMajorVersion($this->device->getClient()['version']);
            if ('Firefox' == $browser && $version < 60) {
                $event->setResponse($this->createRedirectResponse());

                return;
            }

            if ('Chrome' == $browser && $version < 61) {
                $event->setResponse($this->createRedirectResponse());

                return;
            }

            if ('Edge' == $browser && $version < 18) {
                $event->setResponse($this->createRedirectResponse());

                return;
            }

            if ('Opera' == $browser && $version < 48) {
                $event->setResponse($this->createRedirectResponse());

                return;
            }

            // TODO détecter ios (10+) & Android(4.4+)

            if ('Internet Explorer' == $browser) {
                $event->setResponse($this->createRedirectResponse());

                return;
            }
        }
    }

    protected function createRedirectResponse(): Response
    {
        return new Response(
            $this->twig->render('navigateur-non-pris-en charge.html.twig')
        );
    }

    private static function extractMajorVersion(string $version): ?int
    {
        preg_match("/\d+\./", $version, $match, PREG_UNMATCHED_AS_NULL);

        return isset($match[0]) ? intval($match[0]) : null;
    }
}
