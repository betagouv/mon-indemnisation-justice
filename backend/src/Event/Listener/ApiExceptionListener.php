<?php

namespace MonIndemnisationJustice\Event\Listener;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[AsEventListener]
class ApiExceptionListener
{
    public function __invoke(ExceptionEvent $event, #[Autowire('%env(APP_ENV)')] string $env): void
    {
        if (str_starts_with($event->getRequest()->getRequestUri(), '/api/') && (!str_starts_with($env, 'prod') || !$event->getRequest()->query->has('_debug'))) {
            $exception = $event->getThrowable();

            $event->setResponse(new JsonResponse([
                'erreur' => $exception->getMessage(),
            ], $exception instanceof HttpException ? $exception->getStatusCode() : Response::HTTP_INTERNAL_SERVER_ERROR));
        }
    }
}
