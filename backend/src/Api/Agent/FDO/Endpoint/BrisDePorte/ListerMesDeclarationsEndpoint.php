<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Endpoint\BrisDePorte;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Agent\FDO\Transformers\DeclarationFDOBrisPorteOutputMapper;
use MonIndemnisationJustice\Api\Agent\FDO\Voter\DeclarationFDOBrisPorteVoter;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Route API qui permet à un agent des FDO de consulter ses déclarations d'erreur opérationnelle.
 */
#[Route('/api/agent/fdo/erreur-operationnelle/mes-declarations', name: 'api_agent_fdo_bris_porte_mes_declaration_lister', methods: ['GET'])]
#[IsGranted(
    DeclarationFDOBrisPorteVoter::ACTION_LISTER,
    message: "L'accès aux déclarations d'erreur opérationnelle est retreinte aux agents des Forces de l'Ordre",
    statusCode: Response::HTTP_FORBIDDEN
)]
class ListerMesDeclarationsEndpoint
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ObjectMapperInterface $objectMapper,
        protected readonly NormalizerInterface $normalizer,
    ) {}

    public function __invoke(Security $security): Response
    {
        /** @var Agent $agent */
        $agent = $security->getUser();
        $declarations = $this->em->getRepository(DeclarationFDOBrisPorte::class)->findBy(['agent' => $agent]);

        return new JsonResponse(
            $this->normalizer->normalize(
                array_map(
                    fn (DeclarationFDOBrisPorte $declaration) => DeclarationFDOBrisPorteOutputMapper::mapper($declaration, $this->objectMapper),
                    $declarations
                ),
                'json'
            )
        );
    }
}
