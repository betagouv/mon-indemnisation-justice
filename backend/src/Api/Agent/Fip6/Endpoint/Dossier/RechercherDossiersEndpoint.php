<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Agent\Fip6\Output\DossierApercuOutput;
use MonIndemnisationJustice\Api\Agent\Fip6\Voter\DossierVoter;
use MonIndemnisationJustice\Api\Requerant\Dossier\Normalization\EntityResolveur;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Repository\DossierRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/api/agent/fip6/dossiers/rechercher', name: 'api_agent_fip6_dossiers_rechercher', methods: ['GET'])]
#[IsGranted(DossierVoter::ACTION_RECHERCHER, message: 'Seul un agent habilité peut rechercher des dossiers', statusCode: Response::HTTP_FORBIDDEN)]
class RechercherDossiersEndpoint
{
    public function __invoke(
        #[MapQueryString] RechercherDossiersInput $input,
        DossierRepository $dossierRepository,
        EntityManagerInterface $em,
        NormalizerInterface $normalizer,
    ): Response {
        EntityResolveur::configurer($em);

        $paginator = $dossierRepository->rechercheDossiers(
            page: $input->page(),
            taille: $input->taille(),
            etats: $input->etats(),
            attributaires: $input->attributaires(),
            filtres: $input->filtres(),
            nonAttribue: $input->nonAttribue(),
        );

        return new JsonResponse(
            [
                'page' => $input->page(),
                'taille' => $input->taille(),
                'total' => $paginator->count(),
                'resultats' => $normalizer->normalize(
                    array_map(
                        fn (Dossier $dossier) => DossierApercuOutput::creerDepuisDossier($dossier),
                        iterator_to_array(
                            $paginator->getIterator()
                        ) ?? [],
                    ),
                    'json'
                ),
            ]
        );
    }
}
