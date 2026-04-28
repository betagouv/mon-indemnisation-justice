<?php

namespace MonIndemnisationJustice\Controller;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Dto\ModificationMotDePasse;
use MonIndemnisationJustice\Dto\MotDePasseOublieDto;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\Usager;
use MonIndemnisationJustice\Forms\ModificationMotDePasseType;
use MonIndemnisationJustice\Forms\MotDePasseOublieType;
use MonIndemnisationJustice\Repository\RequerantRepository;
use MonIndemnisationJustice\Security\Authenticator\FranceConnectAuthenticator;
use MonIndemnisationJustice\Security\Oidc\OidcClient;
use MonIndemnisationJustice\Service\Mailer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    public function __construct(
        protected UrlGeneratorInterface $urlGenerator,
        protected UserPasswordHasherInterface $userPasswordHasher,
        protected AuthenticationUtils $authenticationUtils,
        protected Mailer $mailer,
        protected EntityManagerInterface $em,
        protected readonly RequerantRepository $requerantRepository,
        #[Autowire(service: 'oidc_client_france_connect')]
        protected readonly OidcClient $oidcClientRequerant,
    ) {
    }

    #[Route(path: '/connexion', name: 'securite_connexion', methods: ['GET', 'POST'])]
    public function login(Request $request): Response
    {
        if ($this->getUser() instanceof Agent) {
            return $this->redirectToRoute('agent_index');
        }

        if ($this->getUser() instanceof Usager) {
            return $this->redirectToRoute('requerant_home_index');
        }

        $erreurConnexion = $this->authenticationUtils->getLastAuthenticationError();
        $lastUsername = $this->authenticationUtils->getLastUsername();

        if ($erreurConnexion instanceof TooManyLoginAttemptsAuthenticationException) {
            $errorMessage = 'Trop de tentatives de connexion, veuillez attendre quelques minutes avant de ré-essayer';
        } elseif ($erreurConnexion instanceof BadCredentialsException) {
            $errorMessage = 'Identifiants invalides';
        } elseif ($erreurConnexion && $erreurConnexion->getMessage()) {
            $errorMessage = $erreurConnexion->getMessage();
        } else {
            $errorMessage = $request->getSession()->getFlashBag()->get('erreur_identification', [''])[0] ?? null;
        }

        return $this->render('security/connexion.html.twig', [
            'last_username' => $request->getSession()->getFlashBag()->get('connexion', [null])[0] ?? $lastUsername,
            'error_message' => $erreurConnexion instanceof BadCredentialsException ? $errorMessage : null,
            'france_connect_url' => $this->oidcClientRequerant->buildAuthorizeUrl($request, 'securite_usager_connexion'),
            'mdp_oublie_form' => $this->createForm(MotDePasseOublieType::class, new MotDePasseOublieDto()),
            'message_erreur_connexion' => $erreurConnexion instanceof BadCredentialsException ? null : $errorMessage,
        ]);
    }

    #[Route(path: '/connexion-requerant', name: 'securite_connexion_requerant', methods: ['POST'])]
    public function connexionRequerant(): Response
    {
        // On ne devrait jamais arriver ici, l'authentificateur form_login étant configuré pour écouter cette route
        return $this->redirectToRoute('securite_connexion');
    }

    #[Route('/connexion/usager', name: 'securite_usager_connexion', methods: ['GET', 'POST'])]
    public function connexionFranceConnect(Request $request, #[Autowire(service: 'oidc_client_france_connect')] OidcClient $franceConnectOidcClient): Response
    {
        if (
            $request->isMethod(Request::METHOD_POST)
            && $this->isCsrfTokenValid('connexionUsager', $request->getPayload()->get('_csrf_token_usager'))
        ) {
            return $this->redirect($franceConnectOidcClient->buildAuthorizeUrl($request, 'securite_usager_connexion'));
        }

        // On ne devrait jamais arriver ici, l'authentificateur France Connect étant configuré pour écouter cette route
        return $this->redirectToRoute('securite_connexion');
    }

    #[Route('/inscription/usager', name: 'securite_usager_inscription', methods: ['GET'])]
    public function inscriptionFranceConnect(): Response
    {
        // On ne devrait jamais arriver ici, l'authentificateur France Connect étant configuré pour écouter cette route
        return $this->redirectToRoute('securite_connexion');
    }

    #[Route('/deconnexion/usager', name: 'securite_usager_deconnexion', methods: ['GET'])]
    public function deconnexionFranceConnect(Request $request, TokenStorageInterface $tokenStorage, FranceConnectAuthenticator $authenticator): Response
    {
        $authenticator->logout($request);
        $tokenStorage->setToken(null);

        return $this->redirectToRoute('app_logout');
    }

    #[Route('/agent/connexion', name: 'agent_securite_connexion', methods: ['GET'])]
    #[IsGranted('PUBLIC_ACCESS')]
    #[Route('/connexion/agent', name: 'securite_agent_connexion', methods: ['GET', 'POST'])]
    public function connexionProConnect(Request $request, #[Autowire(service: 'oidc_client_pro_connect')] OidcClient $proConnectOidcClient): Response
    {
        if (
            $request->isMethod(Request::METHOD_POST)
            && $this->isCsrfTokenValid('connexionAgent', $request->getPayload()->get('_csrf_token_agent'))
        ) {
            return $this->redirect($proConnectOidcClient->buildAuthorizeUrl($request, 'securite_agent_connexion'));
        }

        // On ne devrait jamais arriver ici, l'authentificateur Pro Connect étant configuré pour écouter cette route
        return $this->redirectToRoute('securite_connexion');
    }

    #[Route(path: '/deconnexion', name: 'app_logout')]
    public function logout(): Response
    {
        return $this->redirectToRoute('securite_connexion');
    }

    #[Route(path: '/connexion/mot-de-passe-oublie', name: 'securite_connexion_mdp_oublie', methods: ['POST'])]
    public function motDePasseOublie(Request $request): Response
    {
        $form = $this->createForm(MotDePasseOublieType::class, new MotDePasseOublieDto());

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $requerant = $this->em->getRepository(Usager::class)->findOneBy([
                    'email' => $form->getData()->email,
                    'estVerifieCourriel' => true,
                ]);

                if ($requerant) {
                    // Génération d'un jeton de vérification
                    $requerant->genererJetonVerification();

                    $this->em->persist($requerant);
                    $this->em->flush();

                    // Envoi du mail de confirmation.
                    $this->mailer
                        ->toRequerant($requerant)
                        ->subject('Mon Indemnisation Justice: réinitialisation de votre mot de passe')
                        ->htmlTemplate('email/mot_de_passe_oublie.html.twig', [
                            'requerant' => $requerant,
                        ])
                        ->send();
                }

                $this->addFlash('success', [
                    'title' => 'Demande bien reçue',
                    'message' => "Si l'adresse fournie correspond à un compte actif, vous recevrez dans quelques instants un courriel vous invitant à saisir un nouveau mot de passe.",
                ]);
            }
        }

        return $this->redirectToRoute('securite_connexion');
    }

    #[Route(path: '/connexion/changer-mon-mot-de-passe/{jeton}', name: 'securite_connexion_changer_mdp', methods: ['GET', 'POST'])]
    public function reset_password(Request $request, string $jeton): Response
    {
        /** @var Usager $requerant */
        $requerant = $this->requerantRepository->findOneBy(['jetonVerification' => $jeton]);
        if (null === $requerant) {
            return $this->redirectToRoute('securite_connexion');
        }

        $modificationMotDePasse = new ModificationMotDePasse();

        $form = $this->createForm(ModificationMotDePasseType::class, $modificationMotDePasse);
        $errors = [];

        if (Request::METHOD_POST === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isSubmitted()) {
                if ($form->isValid()) {
                    $modificationMotDePasse = $form->getData();
                    $requerant->setPassword(
                        $this->userPasswordHasher->hashPassword(
                            $requerant,
                            $modificationMotDePasse->motDePasse
                        )
                    );
                    $requerant->supprimerJetonVerification();

                    $this->em->persist($requerant);
                    $this->em->flush();
                    $this->addFlash('success', [
                        'title' => 'Mot de passe modifié',
                        'message' => 'Le mot de passe a été modifié avec succès !',
                    ]);

                    return $this->redirectToRoute('securite_connexion');
                }

                /** @var FormError $error */
                foreach ($form->getErrors(true) as $key => $error) {
                    $errors[$error->getOrigin()?->getName()] = $error->getMessage();
                }
            }
        }

        return $this->render('security/reset_password.html.twig', [
            'requerant' => $requerant,
            'form' => $form,
            'errors' => $errors,
        ]);
    }
}
