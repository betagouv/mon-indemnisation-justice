<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Endpoint\BrisDePorte;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Agent\FDO\Voter\DeclarationFDOBrisPorteVoter;
use MonIndemnisationJustice\Entity\BrouillonDeclarationFDOBrisPorte;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Route API qui permet à un agent des FDO de déclarer une erreur opérationnelle.
 */
#[Route('/api/agent/fdo/bris-de-porte/{id}/supprimer', name: 'api_agent_fdo_bris_porte_supprimer', methods: ['DELETE'])]
#[IsGranted(
    DeclarationFDOBrisPorteVoter::ACTION_SUPPRIMER,
    'brouillon',
    message: "La déclaration d'une erreur opérationnelle est retreinte aux agents des Forces de l'Ordre",
    statusCode: Response::HTTP_FORBIDDEN
)]
class SupprimerDeclarationBrisPorteEndpoint
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {}

    public function __invoke(Security $security, #[MapEntity] BrouillonDeclarationFDOBrisPorte $brouillon): Response
    {
        $this->em->remove($brouillon);
        $this->em->flush();

        return new JsonResponse(
            [],
            Response::HTTP_NO_CONTENT
        );
    }
}
