<?php

namespace App\Event;

use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Device\AbstractDeviceParser;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

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
            KernelEvents::REQUEST => ['onKernelRequest', 16],
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

        return $match[0] ? intval($match[0]) : null;
    }
}
