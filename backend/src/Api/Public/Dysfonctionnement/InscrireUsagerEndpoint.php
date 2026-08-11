<?php

namespace MonIndemnisationJustice\Api\Public\Dysfonctionnement;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Public\Dysfonctionnement\Input\InscriptionUsagerInput;
use MonIndemnisationJustice\Service\InscriptionUsagerService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(
    '/api/public/dysfonctionnement/inscription',
    name: 'api_public_dysfonctionnement_inscription',
    methods: ['POST'],
    format: 'json',
    env: ['dev', 'test', 'ci', 'develop'],
)]
class InscrireUsagerEndpoint
{
    use ValideDepuisRequeteTrait;

    public const string CSRF_INTENTION = 'inscription-dysfonctionnement';

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SerializerInterface $serializer,
        private readonly ValidatorInterface $validator,
        private readonly InscriptionUsagerService $inscriptionUsagerService,
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
    ) {
    }

    public function __invoke(Request $request): Response
    {
        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken(self::CSRF_INTENTION, $request->headers->get('X-Csrf-Token')))) {
            return new JsonResponse(['erreur' => 'Le jeton CSRF est invalide, veuillez recharger la page.'], Response::HTTP_NOT_ACCEPTABLE);
        }

        $resultat = $this->deserialiserEtValider($request, InscriptionUsagerInput::class, $this->serializer, $this->validator);
        if ($resultat instanceof JsonResponse) {
            return $resultat;
        }
        $input = $resultat;

        $usager = $this->inscriptionUsagerService->creerUsager($input, $request);

        $this->em->flush();

        $this->inscriptionUsagerService->envoyerEmailActivation($usager);

        return new JsonResponse('', Response::HTTP_CREATED);
    }
}
