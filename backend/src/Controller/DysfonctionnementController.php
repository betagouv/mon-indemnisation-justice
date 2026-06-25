<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Controller;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Dto\Inscription;
use MonIndemnisationJustice\Entity\Metadonnees\NavigationRequerant;
use MonIndemnisationJustice\Entity\Personne;
use MonIndemnisationJustice\Entity\TestEligibiliteDysfonctionnement;
use MonIndemnisationJustice\Entity\Usager;
use MonIndemnisationJustice\Security\Oidc\OidcClient;
use MonIndemnisationJustice\Service\Mailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/dysfonctionnement')]
class DysfonctionnementController extends AbstractController
{
    private const CLEF_SESSION_USAGER = 'dysfonctionnement_preinscription_usager';
    private const CSRF_TOKEN_ID = 'dysfonctionnement-creation-de-compte';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly Mailer $mailer,
        #[Autowire(service: 'oidc_client_france_connect')]
        private readonly OidcClient $oidcClientFranceConnect,
    ) {
    }

    #[Route('/creation-de-compte', name: 'dysfonctionnement_creation_de_compte', methods: ['GET'])]
    public function creationDeCompte(
        Request $request,
        NormalizerInterface $normalizer,
        UrlGeneratorInterface $router,
        CsrfTokenManagerInterface $csrfTokenManager,
    ): Response {
        if ($this->getUser() instanceof Usager) {
            return $this->redirectToRoute('requerant_home_index');
        }

        if ($request->getSession()->has(self::CLEF_SESSION_USAGER)) {
            return $this->redirectToRoute('dysfonctionnement_finaliser_la_creation');
        }

        return $this->render('dysfonctionnement/creation_de_compte.html.twig', [
            'react' => [
                'routes' => [
                    'connexion' => $router->generate('securite_connexion'),
                    'inscriptionFranceConnect' => $this->oidcClientFranceConnect->buildAuthorizeUrl($request, 'securite_usager_inscription'),
                    'cgu' => $router->generate('public_cgu'),
                    'creerCompte' => $router->generate('dysfonctionnement_creer_compte'),
                ],
                'token' => $csrfTokenManager->getToken(self::CSRF_TOKEN_ID)->getValue(),
                'inscription' => $normalizer->normalize(new Inscription(), 'json'),
            ],
        ]);
    }

    #[Route('/creer-compte', name: 'dysfonctionnement_creer_compte', methods: ['POST'], format: 'json')]
    public function creerCompte(
        #[MapRequestPayload]
        Inscription $inscription,
        Request $request,
        CsrfTokenManagerInterface $csrfTokenManager,
    ): Response {
        if (!$csrfTokenManager->isTokenValid(new CsrfToken(self::CSRF_TOKEN_ID, $request->headers->get('X-Csrf-Token')))) {
            return new JsonResponse('Le jeton CSRF est invalide.', Response::HTTP_NOT_ACCEPTABLE);
        }

        $usager = (new Usager())
            ->setEmail($inscription->courriel)
            ->setPersonne(
                (new Personne())
                    ->setCivilite($inscription->civilite)
                    ->setPrenom($inscription->prenom)
                    ->setCourriel($inscription->courriel)
                    ->setTelephone($inscription->telephone)
                    ->setNom($inscription->nom)
                    ->setNomNaissance($inscription->nomNaissance ?? $inscription->nom)
            );

        $usager->setPassword($this->userPasswordHasher->hashPassword($usager, $inscription->motDePasse));
        $usager->setRoles([Usager::ROLE_PUBLIC]);
        $usager->genererJetonVerification();

        $testId = $request->getSession()->get('dysfonctionnement_test_eligibilite');
        $test = $testId
            ? $this->entityManager->getRepository(TestEligibiliteDysfonctionnement::class)->find($testId)
            : null;

        if ($test && null === $test->usager) {
            $test->usager = $usager;
            $this->entityManager->persist($test);
        }

        $usager->setNavigation(new NavigationRequerant(idTestEligibilite: $test?->id));

        $this->entityManager->persist($usager);
        $this->entityManager->flush();

        $request->getSession()->set(self::CLEF_SESSION_USAGER, $usager->getId());

        $this->mailer
            ->toRequerant($usager)
            ->subject("Activation de votre compte sur l'application Mon Indemnisation Justice")
            ->htmlTemplate('email/inscription_a_finaliser.html.twig', ['usager' => $usager])
            ->send();

        return new JsonResponse('', Response::HTTP_CREATED);
    }

    #[Route('/finaliser-la-creation', name: 'dysfonctionnement_finaliser_la_creation', methods: ['GET'])]
    public function finaliserLaCreation(Request $request): Response
    {
        $usagerId = $request->getSession()->get(self::CLEF_SESSION_USAGER);
        if (!$usagerId) {
            return $this->redirectToRoute('dysfonctionnement_creation_de_compte');
        }

        $usager = $this->entityManager->getRepository(Usager::class)->find($usagerId);
        if (!$usager) {
            $request->getSession()->remove(self::CLEF_SESSION_USAGER);
            return $this->redirectToRoute('dysfonctionnement_creation_de_compte');
        }

        return $this->render('dysfonctionnement/finaliser_la_creation.html.twig', [
            'email' => $usager->getEmail(),
        ]);
    }
}
