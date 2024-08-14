<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class TestSentryController extends AbstractController
{

    #[Route(name: 'sentry_test_error', path: '/_sentry/error')]
    public function testError()
    {
        // the following code will test if an uncaught exception logs to sentry
        throw new \RuntimeException('Example exception.');
    }
}
