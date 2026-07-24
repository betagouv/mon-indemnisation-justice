<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Agent;

use MonIndemnisationJustice\Api\Agent\Fip6\Output\AgentOutput;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Security\Authenticator\ProConnectAuthenticator;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Route API qui retourne les informations sur l'agent actuellement connecté.
 */
#[Route('/api/agent/fip6/moi', name: 'api_agent_fip6_moi', methods: ['GET'])]
#[Route('/api/agent/fdo/moi', name: 'api_agent_fdo_moi', methods: ['GET'])] // TODO déménagez-moi
class MoiEndpoint
{
    public function __construct(
        private readonly Security $security,
        private readonly NormalizerInterface $normalizer,
        private readonly UrlGeneratorInterface $generateurUrl,
    ) {
    }

    public function __invoke(Request $request): Response
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
                    'urlDeconnexion' => $agentBetaIncarnant ?
                        $this->generateurUrl->generate('agent_fip6_react', ['extra' => 'agents/gestion', '_switch_user' => '_exit'], UrlGeneratorInterface::ABSOLUTE_URL) :
                        $request->getSession()->get(ProConnectAuthenticator::CLEF_SESSION_URL_DECONNEXION) ?? $this->generateurUrl->generate('agent_securite_deconnexion', referenceType: UrlGeneratorInterface::ABSOLUTE_URL),
                ],
                'json'
            )
        );
    }
}
