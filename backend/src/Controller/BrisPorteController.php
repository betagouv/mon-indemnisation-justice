<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Controller;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Dto\Inscription;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\DeclarationErreurOperationnelle;
use MonIndemnisationJustice\Entity\Metadonnees\NavigationRequerant;
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

class PreInscription
{
    public function __construct(
        public ?TestEligibilite $testEligibilite = null,
        public ?DeclarationErreurOperationnelle $declarationErreurOperationnelle = null,
        public ?Requerant $requerant = null,
    ) {}
}

#[Route('/bris-de-porte')]
class BrisPorteController extends AbstractController
{
    public const CLEF_SESSION_TEST_ELIGIBILITE = 'testEligibilite';
    public const CLEF_SESSION_PREINSCRIPTION = 'preinscription';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly Mailer $mailer,
        #[Autowire(service: 'oidc_client_france_connect')]
        protected readonly OidcClient $oidcClientFranceConnect,
    ) {}

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

        $preinscription = $this->getPreinscription($request);
        $testEligibilite = $preinscription->testEligibilite ?? new TestEligibilite();

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
                        ->setTestEligibilite($testEligibilite)
                    ;

                    $this->entityManager->persist($dossier);
                    $this->entityManager->flush();

                    return $this->redirectToRoute('app_bris_porte_edit', ['id' => $dossier->getId()]);
                }

                if (null !== $testEligibilite->requerant) {
                    return $this->redirectToRoute('bris_porte_finaliser_la_creation');
                }

                $preinscription->testEligibilite = $testEligibilite;
                $this->setPreinscription($request, $preinscription);

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

    #[Route('/invitation/{reference}', name: 'bris_porte_demarrer_depuis_invitation', methods: ['GET'])]
    /**
     * Route depuis laquelle atterrissent les requérants qui sont invités à déposer suite à la déclaration d'erreur
     * opérationnelle de la part d'un agent des FDO.
     *
     * Ici, on fait le choix délibéré de ne pas utiliser de `MapEntity` pour l'instance de la déclaration associée à la
     * référénce donnée pour pouvoir, à terme, contrôler le nombre de tentatives effectuées et ainsi appliquer un _rate
     * limit_.
     *
     * Idem avec la `$reference` pour laquelle aucun `requirement` n'est défini sur la route puisqu'on souhaite intégrer
     * toutes les tentatives au quota de l'utilisateur courant.
     */
    public function demarrerDepuisInvitation(Request $request, string $reference): Response
    {
        if (!preg_match('/[A-Z0-9]{6}/', $reference)) {
            // TODO compter la tentative pour le rate limiter
            return $this->redirectToRoute('app_homepage');
        }

        $preinscription = $this->getPreinscription($request);
        $declaration = $this->entityManager->getRepository(DeclarationErreurOperationnelle::class)->findOneBy(['reference' => $reference]);

        if ($declaration->estAttribue()) {
            // TODO compter la tentative pour le rate limiter
            return $this->redirectToRoute('app_homepage');
        }

        $preinscription->declarationErreurOperationnelle = $declaration;
        $this->setPreinscription($request, $preinscription);

        return $this->redirectToRoute('bris_porte_creation_de_compte');
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

        $inscription = new Inscription();
        $preinscription = $this->getPreinscription($request);

        if (null !== $preinscription->requerant) {
            return $this->redirectToRoute('bris_porte_finaliser_la_creation');
        }

        if (null === $preinscription->declarationErreurOperationnelle) {
            if (null === $preinscription->testEligibilite) {
                return $this->redirectToRoute('bris_porte_tester_eligibilite');
            }
        } else {
            $infosRequerant = $preinscription->declarationErreurOperationnelle->getInfosRequerant();
            $inscription->nom = $infosRequerant->nom;
            $inscription->nomNaissance = $infosRequerant->nom;
            $inscription->prenom = $infosRequerant->prenom;
            $inscription->courriel = $infosRequerant->courriel;
            $inscription->telephone = $infosRequerant->telephone;
        }

        return $this->render('brisPorte/creation_de_compte.html.twig', [
            'react' => [
                'routes' => [
                    'connexion' => $router->generate('app_login'),
                    'inscriptionFranceConnect' => $this->oidcClientFranceConnect->buildAuthorizeUrl($request, 'requerant_securite_inscription'),
                    'cgu' => $router->generate('public_cgu'),
                ],
                'token' => $csrfTokenManager->getToken('creation-de-compte')->getValue(),
                'inscription' => $normalizer->normalize($inscription, 'json'),
            ],
        ]);
    }

    #[Route(path: '/creer-compte', name: 'bris_porte_creation_de_compte_json', methods: ['POST'], format: 'json')]
    public function creerCompteJson(
        #[MapRequestPayload]
        Inscription $inscription,
        Request $request,
        CsrfTokenManagerInterface $csrfTokenManager,
    ): Response {
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('creation-de-compte', $request->headers->get('X-Csrf-Token')))) {
            return new JsonResponse('Le jeton CSRF est invalide.', Response::HTTP_NOT_ACCEPTABLE);
        }

        $preinscription = $this->getPreinscription($request);
        $testEligibilite = $preinscription->testEligibilite;

        /** @var DeclarationErreurOperationnelle $declaration */
        $declaration = $preinscription->declarationErreurOperationnelle;

        // Création du compte requérant
        $requerant = (new Requerant())
            ->setEmail($inscription->courriel)
        ;
        $requerant->getPersonnePhysique()
            ->setCivilite($inscription->civilite)
            ->setPrenom1($inscription->prenom)
            ->setEmail($inscription->courriel)
            ->setTelephone($inscription->telephone)
            ->setNom($inscription->nom)
            ->setNomNaissance($inscription->nomNaissance ?? $inscription->nom)
        ;
        $requerant->setPassword(
            $this->userPasswordHasher->hashPassword(
                $requerant,
                $inscription->motDePasse
            )
        );
        $requerant->genererJetonVerification();
        $requerant->setNavigation(new NavigationRequerant(
            idTestEligibilite: $testEligibilite?->id,
            idDeclaration: $declaration?->getId(),
        ));

        $this->entityManager->persist($requerant);

        if (null !== $testEligibilite) {
            $testEligibilite->requerant = $requerant;
            $this->entityManager->persist($testEligibilite);
        }

        $this->entityManager->flush();

        $preinscription->requerant = $requerant;
        $this->setPreinscription($request, $preinscription);

        // Envoi du mail de confirmation.
        $this->mailer
            ->toRequerant($requerant)
            ->subject("Activation de votre compte sur l'application Mon Indemnisation Justice")
            ->htmlTemplate('email/inscription_a_finaliser.html.twig', [
                'requerant' => $requerant,
            ])
            ->send()
        ;

        return new JsonResponse('', Response::HTTP_CREATED);
    }

    #[Route(path: '/tester-adresse-courriel', name: 'bris_porte_tester_adresse_courriel', methods: ['POST'], format: 'json')]
    public function testerAdresseCourrielJson(Request $request): Response
    {
        $adresse = $request->getPayload()->get('adresse');

        if (!filter_var($adresse, FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse("{$adresse} n'est pas une adresse courriel valide", Response::HTTP_BAD_REQUEST);
        }

        $existant = $this->entityManager->getRepository(Requerant::class)->findOneBy(['email' => $adresse]);

        return new JsonResponse(['disponible' => null === $existant], Response::HTTP_OK);
    }

    #[Route(path: '/finaliser-la-creation', name: 'bris_porte_finaliser_la_creation')]
    public function finaliserLaCreation(Request $request): Response
    {
        $preinscription = $this->getPreinscription($request);

        if (null === $preinscription->declarationErreurOperationnelle) {
            if (null === $preinscription->testEligibilite) {
                return $this->redirectToRoute('bris_porte_tester_eligibilite');
            }

            if (null === $preinscription->requerant) {
                return $this->redirectToRoute('bris_porte_creation_de_compte');
            }
        }

        return $this->render(
            'brisPorte/finaliser_la_creation.html.twig',
            [
                'email' => $preinscription->requerant->getEmail(),
            ]
        );
    }

    protected function getPreinscription(Request $request): PreInscription
    {
        $session = $request->getSession()->get(self::CLEF_SESSION_PREINSCRIPTION, []);

        return new PreInscription(
            testEligibilite: $this->chargerEntite(TestEligibilite::class, @$session['testEligibilite'] ?? $request->getSession()->get(self::CLEF_SESSION_TEST_ELIGIBILITE)),
            declarationErreurOperationnelle: $this->chargerEntite(DeclarationErreurOperationnelle::class, @$session['declarationErreurOperationnelle']),
            requerant: $this->chargerEntite(Requerant::class, @$session['requerant']),
        );
    }

    protected function setPreinscription(Request $request, PreInscription $preinscription): void
    {
        $request->getSession()->set(self::CLEF_SESSION_PREINSCRIPTION, [
            'testEligibilite' => $preinscription->testEligibilite?->id,
            'declarationErreurOperationnelle' => $preinscription->declarationErreurOperationnelle?->getId(),
            'requerant' => $preinscription->requerant?->getId(),
        ]);
    }

    protected function chargerEntite(string $class, mixed $id = null): ?object
    {
        return $id ? $this->entityManager->getRepository($class)->find($id) : null;
    }
}
