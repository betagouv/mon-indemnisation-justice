<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Endpoint\BrisDePorte;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Agent\FDO\Dto\DeclarationFDOBrisPorteDto;
use MonIndemnisationJustice\Api\Agent\FDO\Voter\DeclarationFDOBrisPorteVoter;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Route API qui permet à un agent des FDO de consulter ses déclarations d'erreur opérationnelle.
 */
#[Route('/api/agent/fdo/bris-de-porte/mes-declarations', name: 'api_agent_fdo_bris_porte_mes_declaration_lister', methods: ['GET'])]
#[IsGranted(
    DeclarationFDOBrisPorteVoter::ACTION_LISTER,
    message: "L'accès aux déclarations d'erreur opérationnelle est retreinte aux agents des Forces de l'Ordre",
    statusCode: Response::HTTP_FORBIDDEN
)]
#[Serialize]
class ListerMesDeclarationsEndpoint
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
    ) {
    }

    /**
     * @return DeclarationFDOBrisPorteDto[]
     */
    public function __invoke(Security $security): array
    {
        /** @var Agent $agent */
        $agent = $security->getUser();
        $declarations = $this->em->getRepository(DeclarationFDOBrisPorte::class)->findBy(['agent' => $agent]);

        return array_map(
            fn (DeclarationFDOBrisPorte $declaration) => DeclarationFDOBrisPorteDto::depuisDeclaration($declaration),
            $declarations
        );
    }
}
