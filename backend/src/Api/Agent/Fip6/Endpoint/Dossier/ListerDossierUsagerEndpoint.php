<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Agent\Fip6\Output\DossierApercuOutput;
use MonIndemnisationJustice\Api\Agent\Fip6\Output\UsagerOutput;
use MonIndemnisationJustice\Api\Agent\Fip6\Voter\DossierVoter;
use MonIndemnisationJustice\Api\Requerant\Dossier\Normalization\EntityResolveur;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\Usager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/api/agent/fip6/dossiers/usager/{id}', name: 'api_agent_fip6_dossiers_usager', methods: ['GET'])]
#[IsGranted(DossierVoter::ACTION_RECHERCHER, message: 'Seul un agent habilité peut rechercher des dossiers', statusCode: Response::HTTP_FORBIDDEN)]
class ListerDossierUsagerEndpoint
{
    public function __invoke(
        #[MapEntity] Usager $usager,
        EntityManagerInterface $em,
        NormalizerInterface $normalizer,
    ): Response {
        EntityResolveur::configurer($em);


        return new JsonResponse(
            [
                'usager' => $normalizer->normalize(UsagerOutput::depuisUsager($usager), 'json'),
                'dossiers' => $normalizer->normalize($usager->getDossiersBrisDePorte()->map(fn (Dossier $dossier) => DossierApercuOutput::creerDepuisDossier($dossier))->toArray(), 'json'),
            ]
        );
    }
}
