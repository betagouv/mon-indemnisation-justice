<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/meta')]
class MetaController extends AbstractController
{
    public function __construct(
        #[Autowire(env: 'default::MIJ_VERSION')]
        protected readonly ?string $version,
    ) {

    }

    #[Route('/version.json')]
    public function index(): Response
    {
        return new JsonResponse(['version' => $this->version ?? 'dev']);
    }
}
