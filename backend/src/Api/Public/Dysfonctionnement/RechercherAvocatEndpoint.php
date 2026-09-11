<?php

namespace MonIndemnisationJustice\Api\Public\Dysfonctionnement;

use MonIndemnisationJustice\Entity\Avocat;
use MonIndemnisationJustice\Repository\AvocatRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    '/api/public/dysfonctionnement/avocats',
    name: 'api_public_dysfonctionnement_avocats_rechercher',
    methods: ['GET'],
    format: 'json',
    env: ['dev', 'test', 'ci', 'develop'],
)]
class RechercherAvocatEndpoint
{
    public function __construct(
        private readonly AvocatRepository $avocatRepository,
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $recherche = $request->query->get('r');

        $avocats = !empty($recherche) ? $this->avocatRepository->rechercher($recherche) : [];

        return new JsonResponse([
            'resultats' => array_map(
                fn (Avocat $avocat) => [
                    'numeroCnbf' => $avocat->getNumeroCnbf(),
                    'nom' => $avocat->getNom(),
                    'prenom' => $avocat->getPrenom(),
                    'raisonSociale' => $avocat->getRaisonSociale(),
                    'civilite' => $avocat->getCivilite()?->value,
                    'telephone' => $avocat->getTelephone(),
                    'email' => $avocat->getEmail(),
                    'barreau' => ['id' => $avocat->getBarreau()->getId(), 'nom' => $avocat->getBarreau()->getNom()],
                ],
                $avocats
            ),
        ]);
    }
}
