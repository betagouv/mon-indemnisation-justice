<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Endpoint\Etablissement;

use MonIndemnisationJustice\Api\Agent\FDO\Output\EtablissementFDOOutput;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\FDO\EtablissementFDO;
use MonIndemnisationJustice\Repository\EtablissementFDORepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/api/agent/fdo/etablissements/rechercher', name: 'api_agent_fdo_etablissements_rechercher', methods: ['GET'])]
#[IsGranted(
    Agent::ROLE_AGENT_FORCES_DE_L_ORDRE,
    message: "Vous devez être connecté en tant qu'agent des Forces de l'Ordre",
    statusCode: Response::HTTP_FORBIDDEN
)]
class RechercherEtablissementFDOEndpoint
{
    public function __construct(
        protected readonly EtablissementFDORepository $repository,
        protected readonly NormalizerInterface $normalizer,
    ) {
    }

    public function __invoke(
        Security $security,
        Request $request,
    ) {
        /** @var Agent $agent */
        $agent = $security->getUser();
        $recherche = $request->query->get('r');

        $etablissements = !empty($recherche) ? $this->repository->rechercher($agent->getAdministration(), $recherche) : [];

        return new JsonResponse(
            $this->normalizer->normalize(
                array_map(
                    fn (EtablissementFDO $etablissmeent) => EtablissementFDOOutput::depuisEtablissementFDO($etablissmeent),
                    $etablissements
                ),
                'json'
            )
        );
    }
}
