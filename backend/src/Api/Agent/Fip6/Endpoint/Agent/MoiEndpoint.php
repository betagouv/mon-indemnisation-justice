<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Agent;

use MonIndemnisationJustice\Api\Agent\Fip6\Output\AgentOutput;
use MonIndemnisationJustice\Entity\Agent;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
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
    ) {
    }

    public function __invoke(): Response
    {
        /** @var Agent $agentBetaIncarnant */
        $agentBetaIncarnant = null;
        if ($this->security->getToken() instanceof SwitchUserToken) {
            $agentBetaIncarnant = $this->security->getToken()->getOriginalToken()->getUser();
        }

        return new JsonResponse(
            $this->normalizer->normalize(
                [
                    'agent' => AgentOutput::depuisAgent($this->security->getUser()),
                    'incarnePar' => $agentBetaIncarnant?->getNomComplet(true),
                ],
                'json'
            )
        );
    }
}
