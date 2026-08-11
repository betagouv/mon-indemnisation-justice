<?php

namespace MonIndemnisationJustice\Api\Public\Dysfonctionnement;

use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Public\Dysfonctionnement\Input\InscriptionAvocatInput;
use MonIndemnisationJustice\Entity\Avocat;
use MonIndemnisationJustice\Service\InscriptionUsagerService;
use MonIndemnisationJustice\Validation\Constraint\AvocatValide;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route(
    '/api/public/dysfonctionnement/inscription-avocat',
    name: 'api_public_dysfonctionnement_inscription_avocat',
    methods: ['POST'],
    format: 'json',
    env: ['dev', 'test', 'ci', 'develop'],
)]
class InscrireAvocatEndpoint
{
    use ValideDepuisRequeteTrait;

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
        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken(InscrireUsagerEndpoint::CSRF_INTENTION, $request->headers->get('X-Csrf-Token')))) {
            return new JsonResponse(['erreur' => 'Le jeton CSRF est invalide, veuillez recharger la page.'], Response::HTTP_NOT_ACCEPTABLE);
        }

        $resultat = $this->deserialiserEtValider($request, InscriptionAvocatInput::class, $this->serializer, $this->validator);
        if ($resultat instanceof JsonResponse) {
            return $resultat;
        }
        $input = $resultat;

        // La contrainte AvocatValide garantit à ce stade que ce numéro CNBF correspond bien à un Avocat existant.
        $avocat = $this->em->getRepository(Avocat::class)->find($input->numeroCnbf);

        $usager = $this->inscriptionUsagerService->creerUsager($input, $request);
        $usager->setAvocat($avocat);

        try {
            $this->em->flush();
        } catch (UniqueConstraintViolationException) {
            // AvocatValide vérifie par SELECT qu'aucun usager n'est déjà rattaché à ce numéro CNBF, mais ne protège
            // pas contre deux inscriptions concurrentes pour le même avocat : seule la contrainte unique en base
            // (usagers.avocat_id) le fait, en rejetant la seconde des deux transactions à ce flush.
            return new JsonResponse([
                'erreurs' => ['numeroCnbf' => (new AvocatValide())->messageDejaInscrit],
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->inscriptionUsagerService->envoyerEmailActivation($usager);

        return new JsonResponse('', Response::HTTP_CREATED);
    }
}
