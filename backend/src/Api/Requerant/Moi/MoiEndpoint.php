<?php

namespace MonIndemnisationJustice\Api\Requerant\Moi;

use MonIndemnisationJustice\Api\Requerant\Brouillon\Dto\UsagerDto;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\Usager;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Route API qui retourne les informations sur l'agent actuellement connecté.
 */
#[Route('/api/requerant/moi', name: 'api_requerant_moi', methods: ['GET'])]
class MoiEndpoint
{
    public function __construct(
        private readonly NormalizerInterface $normalizer,
        private readonly ObjectMapperInterface $mapper,
    ) {
    }

    public function __invoke(Security $security): Response
    {
        $usager = $security->getUser();

        if (!$usager instanceof Usager) {
            throw new UnauthorizedHttpException('Service réservé aux requérants');
        }

        /** @var Agent $agentBetaIncarnant */
        $agentBetaIncarnant = null;
        if ($security->getToken() instanceof SwitchUserToken) {
            $agentBetaIncarnant = $security->getToken()->getOriginalToken()->getUser();
        }

        return new JsonResponse(
            $this->normalizer->normalize(
                [
                    'usager' => $this->mapper->map($security->getUser(), UsagerDto::class),
                    'incarnePar' => $agentBetaIncarnant?->getNomComplet(true),
                ],
                'json',
            )
        );
    }
}
