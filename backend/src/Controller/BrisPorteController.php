<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Controller;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Dto\Inscription;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\Metadonnees\NavigationRequerant;
use MonIndemnisationJustice\Entity\Personne;
use MonIndemnisationJustice\Entity\RapportAuLogement;
use MonIndemnisationJustice\Entity\TestEligibiliteBrisPorte;
use MonIndemnisationJustice\Entity\Usager;
use MonIndemnisationJustice\Forms\TestEligibiliteBrisPorteType;
use MonIndemnisationJustice\Security\Oidc\OidcClient;
use MonIndemnisationJustice\Service\ConstructeurUsagerDepuisDeclaration;
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
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PreInscription
{
    public function __construct(
        public ?TestEligibiliteBrisPorte $testEligibilite = null,
        public ?DeclarationFDOBrisPorte $declarationErreurOperationnelle = null,
        public ?Usager $requerant = null,
    ) {
    }
}

#[Route('/bris-de-porte')]
class BrisPorteController extends AbstractController
{
    public const CLEF_SESSION_TEST_ELIGIBILITE = 'testEligibilite';
    public const CLEF_SESSION_PREINSCRIPTION = 'preinscription';
    public const CLEF_SESSION_INVITATION_FRANCE_CONNECT = 'invitation_france_connect';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly Mailer $mailer,
        #[Autowire(service: 'oidc_client_france_connect')]
        protected readonly OidcClient $oidcClientFranceConnect,
        private readonly ConstructeurUsagerDepuisDeclaration $constructeurUsagerDepuisDeclaration,
    ) {
    }

    #[Route('/tester-mon-eligibilite', name: 'bris_porte_tester_eligibilite', methods: ['GET', 'POST'])]
    public function testerMonEligibilite(Request $request): Response
    {
        $usager = $this->getUser();
        if ($usager instanceof Usager) {
            /** @var Usager $usager */
            if (null !== $usager->getDernierDossier() && !$usager->getDernierDossier()->estDepose()) {
                return $this->redirectToRoute('requerant_react', ['extra' => "dossier/bris-de-porte/{$usager->getDernierDossier()->getId()}/"]);
            }

            // Sinon, on poursuit le test d'éligibilité en vue de créer un nouveau dossier.
        }

        $preinscription = $this->getPreinscription($request);
        $testEligibilite = $preinscription->testEligibilite ?? new TestEligibiliteBrisPorte();

        if ($request->getSession()->has(AtterrissageController::SESSION_KEY)) {
            $testEligibilite->estIssuAttestation = true;

            $request->getSession()->remove(AtterrissageController::SESSION_KEY);
        }

        $form = $this->createForm(TestEligibiliteBrisPorteType::class, $testEligibilite);

        if (Request::METHOD_POST === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                /** @var TestEligibiliteBrisPorte $testEligibilite */
                $testEligibilite = $form->getData();

                $testEligibilite->estEligibleExperimentation = true;
                $testEligibilite->dateSoumission = new \DateTimeImmutable();

                $this->entityManager->persist($testEligibilite);
                $this->entityManager->flush();

                if ($usager instanceof Usager) {
                    $testEligibilite->usager = $usager;
                    $dossier = Dossier::brisDePorteDepuisTestEligibilite($testEligibilite);


                    $this->entityManager->persist($dossier);
                    $this->entityManager->flush();

                    return $this->redirectToRoute('requerant_react', ['extra' => "dossier/bris-de-porte/{$dossier->getId()}/"]);
                }

                if (null !== $testEligibilite->usager) {
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
    public function demarrerDepuisInvitation(
        Request $request,
        string $reference,
        UrlGeneratorInterface $router,
        CsrfTokenManagerInterface $csrfTokenManager,
    ): Response {
        if (!preg_match('/^[a-f0-9]{32}$/', $reference)) {
            // TODO compter la tentative pour le rate limiter
            return $this->redirectToRoute('app_homepage');
        }

        $declaration = $this->entityManager->getRepository(DeclarationFDOBrisPorte::class)->findOneBy(['reference' => $reference]);

        if (null === $declaration || $declaration->estAttribue()) {
            // TODO compter la tentative pour le rate limiter
            return $this->redirectToRoute('app_homepage');
        }

        // Portée en session le temps de l'aller-retour vers FranceConnect : FranceConnectAuthenticator vérifiera que
        // l'identité obtenue correspond à celle de cette déclaration.
        $request->getSession()->set(self::CLEF_SESSION_INVITATION_FRANCE_CONNECT, $reference);

        // Pas de redirection : l'URL de l'invitation reste affichée, et porte elle-même le lien avec la déclaration.
        // Les informations du requérant sont déjà connues (saisies par l'agent) : seul un mot de passe est demandé.
        return $this->render('brisPorte/creation_de_compte.html.twig', [
            'react' => [
                'routes' => [
                    'creerEspace' => $router->generate('bris_porte_creer_espace_json', ['reference' => $reference]),
                    'finaliserLaCreation' => $router->generate('bris_porte_finaliser_la_creation'),
                    'inscriptionFranceConnect' => $this->oidcClientFranceConnect->buildAuthorizeUrl($request, 'securite_usager_inscription'),
                    'cgu' => $router->generate('public_cgu'),
                ],
                'token' => $csrfTokenManager->getToken('creer-espace')->getValue(),
                'identifiant' => $declaration->getCoordonneesRequerant()?->getCourriel(),
                'erreur' => $request->getSession()->getFlashBag()->get('erreur_identification')[0] ?? null,
            ],
        ]);
    }

    /**
     * Création du compte à partir d'une invitation : les informations du requérant viennent uniquement de la
     * déclaration en base (saisies par l'agent). Seuls le mot de passe et l'acceptation des CGU sont demandés, donc
     * pas de DTO dédié : les trois valeurs sont lues et validées directement.
     *
     * Le mot de passe est encodé en base64 côté client avant l'envoi, puis décodé ici avant d'être haché par Symfony.
     */
    #[Route(path: '/invitation/{reference}/creer-espace', name: 'bris_porte_creer_espace_json', methods: ['POST'], format: 'json')]
    public function creerEspaceJson(Request $request, string $reference, CsrfTokenManagerInterface $csrfTokenManager, ValidatorInterface $validator): Response
    {
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('creer-espace', $request->headers->get('X-Csrf-Token')))) {
            return new JsonResponse('Le jeton CSRF est invalide.', Response::HTTP_NOT_ACCEPTABLE);
        }

        if (!preg_match('/^[a-f0-9]{32}$/', $reference)) {
            return new JsonResponse('Lien d\'invitation invalide.', Response::HTTP_NOT_FOUND);
        }

        $declaration = $this->entityManager->getRepository(DeclarationFDOBrisPorte::class)->findOneBy(['reference' => $reference]);

        if (null === $declaration || $declaration->estAttribue()) {
            return new JsonResponse('Lien d\'invitation invalide.', Response::HTTP_NOT_FOUND);
        }

        $coordonneesRequerant = $declaration->getCoordonneesRequerant();
        if (null === $coordonneesRequerant) {
            return new JsonResponse('Aucune information n\'est associée à cette invitation.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $donnees = $request->getPayload();
        $motDePasse = base64_decode($donnees->get('motDePasse', ''), true) ?: '';
        $confirmation = base64_decode($donnees->get('confirmation', ''), true) ?: '';
        $cguOk = true === $donnees->get('cguOk');

        $violations = $validator->validate($motDePasse, [
            new Assert\Length(min: 8, minMessage: 'Votre mot de passe doit contenir au moins 8 caractères'),
            new Assert\Regex('/\d/', message: 'Votre mot de passe doit contenir au moins 1 chiffre'),
            new Assert\Regex('/[^a-zA-Z0-9]/', message: 'Votre mot de passe doit contenir au moins 1 caractère spécial'),
        ]);
        if (count($violations) > 0) {
            return new JsonResponse($violations->get(0)->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        if ($motDePasse !== $confirmation) {
            return new JsonResponse('Les deux mots de passe doivent être identiques', Response::HTTP_UNPROCESSABLE_ENTITY);
        }
        if (!$cguOk) {
            return new JsonResponse('Vous devez accepter les conditions générales d\'utilisation', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $usager = $this->constructeurUsagerDepuisDeclaration->construire($declaration);

        $violations = $validator->validate($usager);
        if (count($violations) > 0) {
            return new JsonResponse($violations->get(0)->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // Le mot de passe décodé est haché par Symfony, comme pour la création de compte générale
        $usager->setPassword($this->userPasswordHasher->hashPassword($usager, $motDePasse));
        $usager->genererJetonVerification();

        $this->entityManager->persist($usager);
        $this->entityManager->flush();

        $preinscription = $this->getPreinscription($request);
        $preinscription->requerant = $usager;
        $this->setPreinscription($request, $preinscription);

        $this->mailer
            ->toRequerant($usager)
            ->subject("Activation de votre compte sur l'application Mon Indemnisation Justice")
            ->htmlTemplate('email/inscription_a_finaliser.html.twig', [
                'usager' => $usager,
            ])
            ->send();

        return new JsonResponse('', Response::HTTP_CREATED);
    }

    #[Route(path: '/creation-de-compte', name: 'bris_porte_creation_de_compte', methods: ['GET'])]
    public function creationDeCompte(
        Request $request,
        NormalizerInterface $normalizer,
        UrlGeneratorInterface $router,
        CsrfTokenManagerInterface $csrfTokenManager,
    ): Response {
        if ($this->getUser() instanceof Usager) {
            return $this->redirectToRoute('requerant_home_index');
        }

        $inscription = new Inscription();
        $preinscription = $this->getPreinscription($request);

        if (null !== $preinscription->requerant) {
            return $this->redirectToRoute('bris_porte_finaliser_la_creation');
        }

        if (null === $preinscription->testEligibilite) {
            return $this->redirectToRoute('bris_porte_tester_eligibilite');
        }

        return $this->render('brisPorte/creation_de_compte.html.twig', [
            'react' => [
                'routes' => [
                    'connexion' => $router->generate('securite_connexion'),
                    'inscriptionFranceConnect' => $this->oidcClientFranceConnect->buildAuthorizeUrl($request, 'securite_usager_inscription'),
                    'cgu' => $router->generate('public_cgu'),
                ],
                'token' => $csrfTokenManager->getToken('creation-de-compte')->getValue(),
                'inscription' => $normalizer->normalize($inscription, 'json'),
                'franceConnect' => !(RapportAuLogement::BAILLEUR_SOCIAL === $preinscription->testEligibilite?->rapportAuLogement),
            ],
        ]);
    }

    #[Route(path: '/creer-compte', name: 'bris_porte_creation_de_compte_json', methods: ['POST'], format: 'json')]
    public function creerCompteJson(
        #[MapRequestPayload]
        Inscription $inscription,
        Request $request,
        CsrfTokenManagerInterface $csrfTokenManager,
        ValidatorInterface $validator,
    ): Response {
        if (!$csrfTokenManager->isTokenValid(new CsrfToken('creation-de-compte', $request->headers->get('X-Csrf-Token')))) {
            return new JsonResponse('Le jeton CSRF est invalide.', Response::HTTP_NOT_ACCEPTABLE);
        }

        $preinscription = $this->getPreinscription($request);
        $testEligibilite = $preinscription->testEligibilite;

        // Création du compte requérant
        $usager = new Usager()
            ->setEmail($inscription->courriel ?? '')
            ->setPersonne(
                new Personne()
                    ->setCivilite($inscription->civilite)
                    ->setPrenom($inscription->prenom)
                    ->setCourriel($inscription->courriel ?? '')
                    ->setTelephone($inscription->telephone)
                    ->setNom($inscription->nom)
                    ->setNomNaissance($inscription->nomNaissance ?? $inscription->nom)
            );

        // La validation du courriel (obligatoire, valide, unique) est portée par l'entité
        $violations = $validator->validate($usager);
        if (count($violations) > 0) {
            return new JsonResponse($violations->get(0)->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }


        $usager->setPassword(
            $this->userPasswordHasher->hashPassword(
                $usager,
                $inscription->motDePasse
            )
        );
        $usager->genererJetonVerification();
        $usager->setNavigation(new NavigationRequerant(
            idTestEligibilite: $testEligibilite?->id,
        ));

        $this->entityManager->persist($usager);

        if (null !== $testEligibilite) {
            $testEligibilite->usager = $usager;
            $this->entityManager->persist($testEligibilite);
        }

        $this->entityManager->flush();

        $preinscription->requerant = $usager;
        $this->setPreinscription($request, $preinscription);

        // Envoi du mail de confirmation.
        $this->mailer
            ->toRequerant($usager)
            ->subject("Activation de votre compte sur l'application Mon Indemnisation Justice")
            ->htmlTemplate('email/inscription_a_finaliser.html.twig', [
                'usager' => $usager,
            ])
            ->send();

        return new JsonResponse('', Response::HTTP_CREATED);
    }

    #[Route(path: '/tester-adresse-courriel', name: 'bris_porte_tester_adresse_courriel', methods: ['POST'], format: 'json')]
    public function testerAdresseCourrielJson(Request $request): Response
    {
        $adresse = $request->getPayload()->get('adresse');

        if (!filter_var($adresse, FILTER_VALIDATE_EMAIL)) {
            return new JsonResponse("{$adresse} n'est pas une adresse courriel valide", Response::HTTP_BAD_REQUEST);
        }

        $existant = $this->entityManager->getRepository(Usager::class)->findOneBy(['email' => $adresse]);

        return new JsonResponse(['disponible' => null === $existant], Response::HTTP_OK);
    }

    #[Route(path: '/finaliser-la-creation', name: 'bris_porte_finaliser_la_creation')]
    public function finaliserLaCreation(Request $request): Response
    {
        $preinscription = $this->getPreinscription($request);

        if (null === $preinscription->requerant) {
            if (null === $preinscription->testEligibilite) {
                return $this->redirectToRoute('bris_porte_tester_eligibilite');
            }

            return $this->redirectToRoute('bris_porte_creation_de_compte');
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
            testEligibilite: $this->chargerEntite(TestEligibiliteBrisPorte::class, @$session['testEligibilite'] ?? $request->getSession()->get(self::CLEF_SESSION_TEST_ELIGIBILITE)),
            declarationErreurOperationnelle: $this->chargerEntite(DeclarationFDOBrisPorte::class, @$session['declarationErreurOperationnelle']),
            requerant: $this->chargerEntite(Usager::class, @$session['requerant']),
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
