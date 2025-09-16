<?php

namespace MonIndemnisationJustice\Event\Listener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

#[AsEventListener]
class ApiExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        if (str_starts_with($event->getRequest()->getRequestUri(), '/api/')) {
            $exception = $event->getThrowable();

            $event->setResponse(new JsonResponse([
                'erreur' => $exception->getMessage(),
            ], $exception instanceof HttpException ? $exception->getStatusCode() : Response::HTTP_INTERNAL_SERVER_ERROR));
        }
    }
}
