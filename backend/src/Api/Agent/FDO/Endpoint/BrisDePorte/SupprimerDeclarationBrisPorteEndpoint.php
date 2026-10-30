<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Endpoint\BrisDePorte;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Agent\FDO\Voter\DeclarationFDOBrisPorteVoter;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Route API qui permet à un agent des FDO de supprimer le brouillon d'une déclaration d'erreur opérationnelle.
 */
#[Route('/api/agent/fdo/bris-de-porte/{declarationId}/supprimer', name: 'api_agent_fdo_bris_porte_supprimer', methods: ['DELETE'])]
#[IsGranted(
    DeclarationFDOBrisPorteVoter::ACTION_SUPPRIMER,
    'declaration',
    message: "La déclaration d'une erreur opérationnelle est retreinte aux agents des Forces de l'Ordre",
    statusCode: Response::HTTP_FORBIDDEN
)]
class SupprimerDeclarationBrisPorteEndpoint
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    public function __invoke(
        #[MapEntity(id: 'declarationId', message: 'Déclaration inconnue')]
        DeclarationFDOBrisPorte $declaration,
    ): Response {
        if (!$declaration->estBrouillon()) {
            return new JsonResponse(['erreur' => 'Cette déclaration ne peut plus être supprimée'], Response::HTTP_FORBIDDEN);
        }

        $this->em->remove($declaration);
        $this->em->flush();

        return new JsonResponse(
            [],
            Response::HTTP_NO_CONTENT
        );
    }
}
