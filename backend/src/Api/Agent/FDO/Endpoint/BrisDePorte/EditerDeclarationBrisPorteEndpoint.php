<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Endpoint\BrisDePorte;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Agent\FDO\Dto\DeclarationFDOBrisPorteDto;
use MonIndemnisationJustice\Api\Agent\FDO\Request\Attribute\MapDeclarationFDO;
use MonIndemnisationJustice\Api\Agent\FDO\Request\Attribute\MapDeclarationFDODto;
use MonIndemnisationJustice\Api\Agent\FDO\Voter\DeclarationFDOBrisPorteVoter;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Route API qui permet à un agent des FDO d'éditer une déclaration d'erreur opérationnelle.
 *
 * Seules les données fournies en entrée sont reportées sur la déclaration, celles manquantes sont ignorées.
 */
#[Route('/api/agent/fdo/bris-de-porte/{declarationId}/editer', name: 'api_agent_fdo_bris_porte_editer', methods: ['PATCH'])]
#[IsGranted(
    DeclarationFDOBrisPorteVoter::ACTION_EDITER,
    'declaration',
    message: "La déclaration d'une erreur opérationnelle est retreinte aux agents des Forces de l'Ordre",
    statusCode: Response::HTTP_FORBIDDEN
)]
class EditerDeclarationBrisPorteEndpoint
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    #[Serialize]
    public function __invoke(
        #[MapDeclarationFDO]
        DeclarationFDOBrisPorte $declaration,
        #[MapDeclarationFDODto]
        DeclarationFDOBrisPorteDto $dto,
    ): DeclarationFDOBrisPorteDto {
        $this->em->persist($declaration);
        $this->em->flush();

        return $dto;
    }
}
