<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Agent;

use MonIndemnisationJustice\Api\Agent\Fip6\Output\AgentOutput;
use MonIndemnisationJustice\Entity\Agent;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Route API qui retourne les informations sur l'agent actuellement connecté.
 */
#[Route('/api/agent/fip6/moi', name: 'api_agent_fip6_moi', methods: ['GET'])]
class MoiEndpoint
{
    public function __construct(
        protected readonly Security $security,
        private readonly NormalizerInterface $normalizer,
        private readonly ObjectMapperInterface $mapper,
    ) {}

    public function __invoke()
    {
        /** @var Agent $agentBetaIncarnant */
        $agentBetaIncarnant = null;
        if ($this->security->getToken() instanceof SwitchUserToken) {
            $agentBetaIncarnant = $this->security->getToken()->getOriginalToken()->getUser();
        }

        return new JsonResponse(
            $this->normalizer->normalize(
                [
                    'agent' => $this->mapper->map($this->security->getUser(), AgentOutput::class),
                    'incarnePar' => $agentBetaIncarnant?->getNomComplet(true),
                ],
                'json'
            )
        );
    }
}
