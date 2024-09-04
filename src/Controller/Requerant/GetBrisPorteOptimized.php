<?php
namespace App\Controller\Requerant;

use App\Entity\BrisPorte;
use App\Entity\Requerant;
use App\Repository\BrisPorteRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[AsController]
#[IsGranted(Requerant::ROLE_REQUERANT)]
class GetBrisPorteOptimized extends AbstractController
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
