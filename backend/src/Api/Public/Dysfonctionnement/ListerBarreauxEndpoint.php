<?php

namespace MonIndemnisationJustice\Api\Public\Dysfonctionnement;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\Barreau;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route(
    '/api/public/dysfonctionnement/barreaux',
    name: 'api_public_dysfonctionnement_barreaux',
    methods: ['GET'],
    format: 'json',
    env: ['dev', 'test', 'ci', 'develop'],
)]
class ListerBarreauxEndpoint
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function __invoke(): JsonResponse
    {
        $barreaux = $this->em->getRepository(Barreau::class)->findBy([], ['nom' => 'ASC']);

        return new JsonResponse(array_map(
            fn (Barreau $barreau) => ['id' => $barreau->getId(), 'nom' => $barreau->getNom()],
            $barreaux
        ));
    }
}
