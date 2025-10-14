<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Agent;

use MonIndemnisationJustice\Api\Agent\Fip6\Output\AgentOutput;
use MonIndemnisationJustice\Entity\Agent;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Route API qui retourne les informations sur l'agent actuellement connecté.
 */
#[Route('/api/agent/fip6/moi', name: 'api_agent_moi', methods: ['GET'])]
// #[IsGranted(AgentVoter::ACTION_LISTER_ACTIFS)]
class MoiEndpoint
{
    public function __construct(
        protected readonly Security $security,
        private readonly NormalizerInterface $normalizer,
        private readonly ObjectMapperInterface $mapper,
    ) {}

    public function __invoke()
    {
        return new JsonResponse(
            $this->normalizer->normalize(
                $this->mapper->map($this->security->getUser(), AgentOutput::class),
                'json'
            )
        );
    }
}
