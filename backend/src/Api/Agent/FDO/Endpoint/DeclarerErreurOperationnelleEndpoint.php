<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Endpoint;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Agent\FDO\Input\DeclarationErreurOperationnelleInput;
use MonIndemnisationJustice\Api\Agent\FDO\Voter\DeclarationErreurOperationelleVoter;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\DeclarationErreurOperationnelle;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Route API qui permet à un agent des FDO de déclarer une erreur opérationnelle.
 */
#[Route('/api/agent/dfo/erreur-operationnelle/declarer', name: 'api_agent_fdo_erreur_operationnelle_declarer', methods: ['PUT'])]
#[IsGranted(
    DeclarationErreurOperationelleVoter::ACTION_DECLARER,
    message: "L'ajout d'un nouvel agent requiert l'habilitation à la gestion des agents",
    statusCode: Response::HTTP_FORBIDDEN
)]
class DeclarerErreurOperationnelleEndpoint
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ObjectMapperInterface $objectMapper,
        protected readonly NormalizerInterface $normalizer,
    ) {}

    public function __invoke(#[MapRequestPayload] DeclarationErreurOperationnelleInput $input, Security $security): Response
    {
        /** @var Agent $agent */
        $agent = $security->getUser();
        $declaration = $this->objectMapper->map($input, DeclarationErreurOperationnelle::class)->setAgent($agent);

        $this->em->persist($declaration);
        $this->em->flush();

        // TODO: envoyer un email

        return new JsonResponse([
            // TODO aligner les schémas avec le frontend
            'id' => $declaration->getId(),
        ], Response::HTTP_CREATED);
    }
}
