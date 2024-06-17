<?php
namespace App\Controller\Prejudice\BrisPorte;

use App\Entity\BrisPorte;
use App\Repository\BrisPorteRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
class GetOptimized extends AbstractController
{
    public function __construct(
      private BrisPorteRepository $brisPorteRepository
    ) {
    }

    public function __invoke(BrisPorte $brisPorte): JsonResponse
    {
        return new JsonResponse($this->brisPorteRepository->get($brisPorte));
    }
}
