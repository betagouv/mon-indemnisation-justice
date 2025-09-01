<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use MonIndemnisationJustice\Dto\Inscription;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\Requerant;
use MonIndemnisationJustice\Entity\TestEligibilite;
use MonIndemnisationJustice\Forms\TestEligibiliteType;
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

#[Route('/bris-de-porte')]
class BrisPorteController extends AbstractController
{
    public const SESSION_CONTEXT_KEY = 'testEligibilite';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly Mailer $mailer,
        #[Autowire(service: 'oidc_client_france_connect')]
        protected readonly OidcClient $oidcClientFranceConnect,
    ) {
    }

    #[Route('/tester-mon-eligibilite', name: 'bris_porte_tester_eligibilite', methods: ['GET', 'POST'])]
    public function testerMonEligibilite(Request $request): Response
    {
        if ($this->getUser() instanceof Requerant) {
            /** @var Requerant $requerant */
            $requerant = $this->getUser();
            if (null !== $requerant->getDernierDossier() && !$requerant->getDernierDossier()->estDepose()) {
                return $this->redirectToRoute('app_bris_porte_edit', ['id' => $requerant->getDernierDossier()->getId()]);
            }

            // Sinon, on poursuit le test d'éligibilité en vue de créer un nouveau dossier.
        }

        $testEligibilite = $this->getTestEligibilite($request) ?? new TestEligibilite();

        if ($request->getSession()->has(AtterrissageController::SESSION_KEY)) {
            $testEligibilite->estIssuAttestation = true;

            $request->getSession()->remove(AtterrissageController::SESSION_KEY);
        }

        $form = $this->createForm(TestEligibiliteType::class, $testEligibilite);

        if (Request::METHOD_POST === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                /** @var TestEligibilite $testEligibilite */
                $testEligibilite = $form->getData();

                $testEligibilite->estEligibleExperimentation = true;
                $testEligibilite->dateSoumission = new \DateTime();

                $this->entityManager->persist($testEligibilite);
                $this->entityManager->flush();

                if (($requerant = $this->getUser()) instanceof Requerant) {
                    $dossier = (new BrisPorte())
                        ->setRequerant($requerant)
                        ->setTestEligibilite($testEligibilite);

                    $this->entityManager->persist($dossier);
                    $this->entityManager->flush();

                    return $this->redirectToRoute('app_bris_porte_edit', ['id' => $dossier->getId()]);
                }

                if (null !== $testEligibilite->requerant) {
                    return $this->redirectToRoute('bris_porte_finaliser_la_creation');
                }

                $this->setTestEligibilite($testEligibilite, $request);

                return $this->redirectToRoute('bris_porte_creation_de_compte');
            }
        }

        return $this->render(
            'brisPorte/tester_mon_eligibilite.html.twig',
            [
                'form' => $form,
            ]
        );
    }

    protected function getTestEligibilite(Request $request): ?TestEligibilite
    {
        $id = $request->getSession()->get(self::SESSION_CONTEXT_KEY, null);

        if ($id) {
            try {
                return $this->entityManager->find(TestEligibilite::class, $id);
            } catch (ORMException $exception) {
                $request->getSession()->remove(self::SESSION_CONTEXT_KEY);
            }
        }

        return null;
    }

    protected function setTestEligibilite(TestEligibilite $testEligibilite, Request $request): void
    {
        $request->getSession()->set(self::SESSION_CONTEXT_KEY, $testEligibilite?->id);
    }

    #[Route(path: '/creation-de-compte', name: 'bris_porte_creation_de_compte', methods: ['GET'])]
    public function creationDeCompte(
        Request $request,
        NormalizerInterface $normalizer,
        UrlGeneratorInterface $router,
        CsrfTokenManagerInterface $csrfTokenManager,
    ): Response {
        if ($this->getUser() instanceof Requerant) {
            return $this->redirectToRoute('requerant_home_index');
        }

        $testEligibilite = $this->getTestEligibilite($request);

        if (null === $testEligibilite) {
            return $this->redirectToRoute('bris_porte_tester_eligibilite');
        }

        if (null !== $testEligibilite->requerant) {
            return $this->redirectToRoute('bris_porte_finaliser_la_creation');
        }

        return $this->render('brisPorte/creation_de_compte.html.twig', [
            'react' => [
                'routes' => [
                    'connexion' => $router->generate('app_login'),
                    'inscriptionFranceConnect' => $this->oidcClientFranceConnect->buildAuthorizeUrl($request, 'requerant_securite_inscription'),
                    'cgu' => $router->generate('public_cgu'),
                ],
                'token' => $csrfTokenManager->getToken('creation-de-compte')->getValue(),
                'inscription' => $normalizer->normalize(new Inscription(), 'json'),
            ],
        ]);
    }

    #[Route(path: '/creer-compte', name: 'bris_porte_creation_de_compte_json', methods: ['POST'], format: 'json')]
    public function creerCompteJson(
        #[MapRequestPayload] Inscription $inscription,
        Request $request,
        CsrfTokenManagerInterface $csrfTokenManager,
    ): Response {
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('creation-de-compte', $request->headers->get('X-Csrf-Token')))) {
            return new JsonResponse('Le jeton CSRF est invalide.', Response::HTTP_NOT_ACCEPTABLE);
        }

        $testEligibilite = $this->getTestEligibilite($request);

        // Création du compte requérant
        $requerant = (new Requerant())
            ->setEmail($inscription->courriel);
        $requerant->getPersonnePhysique()
            ->setCivilite($inscription->civilite)
            ->setPrenom1($inscription->prenom)
            ->setEmail($inscription->courriel)
            ->setTelephone($inscription->telephone)
            ->setNom($inscription->nom)
            ->setNomNaissance($inscription->nomNaissance ?? $inscription->nom);
        $requerant->setPassword(
            $this->userPasswordHasher->hashPassword(
                $requerant,
                $inscription->motDePasse
            )
        );
        $requerant->genererJetonVerification();

        $this->entityManager->persist($requerant);

        $testEligibilite->requerant = $requerant;
        $this->entityManager->persist($testEligibilite);

        $this->entityManager->flush();
        $this->setTestEligibilite($testEligibilite, $request);

        // Envoi du mail de confirmation.
        $this->mailer
            ->toRequerant($requerant)
            ->subject("Activation de votre compte sur l'application Mon Indemnisation Justice")
            ->htmlTemplate('email/inscription_a_finaliser.html.twig', [
                'requerant' => $requerant,
            ])
            ->send();

        return new JsonResponse('', Response::HTTP_CREATED);
    }

    #[Route(path: '/tester-adresse-courriel', name: 'bris_porte_tester_adresse_courriel', methods: ['POST'], format: 'json')]
    public function testerAdresseCourrielJson(Request $request): Response
    {
        $adresse = $request->getPayload()->get('adresse');

        if (!filter_var($adresse, FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse("$adresse n'est pas une adresse courriel valide", Response::HTTP_BAD_REQUEST);
        }

        $existant = $this->entityManager->getRepository(Requerant::class)->findOneBy(['email' => $adresse]);

        return new JsonResponse(['disponible' => null === $existant], Response::HTTP_OK);
    }

    #[Route(path: '/finaliser-la-creation', name: 'bris_porte_finaliser_la_creation')]
    public function finaliserLaCreation(Request $request): Response
    {
        $testEligibilite = $this->getTestEligibilite($request);

        if (null === $testEligibilite) {
            return $this->redirectToRoute('bris_porte_tester_eligibilite');
        } else {
            if (null === $testEligibilite->requerant) {
                return $this->redirectToRoute('bris_porte_creation_de_compte');
            }
        }

        return $this->render(
            'brisPorte/finaliser_la_creation.html.twig',
            [
                'email' => $testEligibilite->requerant->getEmail(),
            ]
        );
    }
}
