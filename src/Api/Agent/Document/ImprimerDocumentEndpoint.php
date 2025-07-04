<?php

namespace MonIndemnisationJustice\Api\Agent\Document;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class ImprimerDocumentEndpoint
{
    public function __construct()
    {
    }

    // TODO voir doc https://symfony.com/doc/current/controller/service.html#invokable-controllers
    public function __invoke(): Response
    {
        return new JsonResponse([
            'test' => 'ok',
        ]);
    }
}
