<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Agent;

use MonIndemnisationJustice\Api\Agent\Fip6\Output\AgentOutput;
use MonIndemnisationJustice\Api\Agent\Fip6\Voter\AgentVoter;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Repository\AgentRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Route API qui permet à un gestionnaire d'agents d'éditer un agent existant.
 */
#[Route('/api/agent/fip6/agents/editer/{id}', name: 'api_agent_agents_editer_agent', methods: ['POST'], requirements: ['id' => '\d+'])]
#[IsGranted(
    AgentVoter::ACTION_EDITER,
    message: "L'édition d'un nouvel agent requiert l'habilitation à la gestion des agents",
    statusCode: 401
)]
class EditerAgentEndpoint
{
    public function __construct(
        protected readonly AgentRepository       $agentRepository,
        protected readonly NormalizerInterface   $normalizer,
        protected readonly ObjectMapperInterface $mapper,
    )
    {
    }

    public function __invoke(#[MapRequestPayload] EditerAgentInput $input, #[MapEntity] Agent $agent)
    {
        $agent = $this->mapper->map($input, $agent);

        $this->agentRepository->save($agent);

        return new JsonResponse(
            $this->normalizer->normalize(
                $this->normalizer->normalize(
                    $this->mapper->map($agent, AgentOutput::class),
                    'json'
                ),
                Response::HTTP_OK
            )
        );
    }
}
