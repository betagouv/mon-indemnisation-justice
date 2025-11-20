<?php

namespace MonIndemnisationJustice\Controller;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Dto\ModificationMotDePasse;
use MonIndemnisationJustice\Dto\MotDePasseOublieDto;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\Requerant;
use MonIndemnisationJustice\Forms\ModificationMotDePasseType;
use MonIndemnisationJustice\Forms\MotDePasseOublieType;
use MonIndemnisationJustice\Repository\RequerantRepository;
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
use Symfony\Component\Security\Core\Exception\TooManyLoginAttemptsAuthenticationException;
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
    ) {}

    #[Route(path: '/connexion', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(Request $request): Response
    {
        $error = $this->authenticationUtils->getLastAuthenticationError();
        $lastUsername = $request->query->get('courriel') ?? $this->authenticationUtils->getLastUsername();

        $errorMessage = '';

        if ($error instanceof TooManyLoginAttemptsAuthenticationException) {
            $errorMessage = 'Trop de tentatives de connexion, veuillez attendre quelques minutes avant de ré-essayer';
        }
        if ($error && $error->getMessage()) {
            $errorMessage = 'Identifiants invalides';
        }

        if ($this->getUser() instanceof Agent) {
            return $this->redirectToRoute('agent_index');
        }

        if ($this->getUser() instanceof Requerant) {
            return $this->redirectToRoute('requerant_home_index');
        }

        return $this->render('security/connexion.html.twig', [
            'last_username' => $request->getSession()->getFlashBag()->get('connexion', [null])[0] ?? $lastUsername,
            'error_message' => $errorMessage,
            'france_connect_url' => $this->oidcClientRequerant->buildAuthorizeUrl($request, 'requerant_securite_connexion'),
            'mdp_oublie_form' => $this->createForm(MotDePasseOublieType::class, new MotDePasseOublieDto()),
            'message_erreur_connexion' => $request->query->get('erreur'),
        ]);
    }

    #[Route(path: '/connexion-requerant', name: 'securite_connexion_requerant', methods: ['POST'])]
    public function connexionRequerant(): Response
    {
        // On ne devrait jamais arriver ici, l'authentificateur form_login étant configuré pour écouter cette route
        return $this->redirectToRoute('app_login');
    }

    #[Route(path: '/connexion-agent', name: 'securite_connexion_agent', methods: ['POST'])]
    public function connexionAgent(#[Autowire(service: 'oidc_client_pro_connect')] OidcClient $proConnectOidcClient, Request $request): Response
    {
        return $this->redirect(
            $proConnectOidcClient->buildAuthorizeUrl($request, 'agent_securite_connexion')
        );
    }

    #[Route(path: '/deconnexion', name: 'app_logout')]
    public function logout(): Response
    {
        return $this->redirectToRoute('app_login');
    }

    #[Route(path: '/mon-mot-de-passe/oublie', name: 'app_send_reset_password', methods: ['POST'])]
    public function motDePasseOublie(Request $request): Response
    {
        $form = $this->createForm(MotDePasseOublieType::class, new MotDePasseOublieDto());

        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $requerant = $this->em->getRepository(Requerant::class)->findOneBy([
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
                        ->send()
                    ;
                }

                $this->addFlash('success', [
                    'title' => 'Demande bien reçue',
                    'message' => "Si l'adresse fournie correspond à un compte actif, vous recevrez dans quelques instants un courriel vous invitant à saisir un nouveau mot de passe.",
                ]);
            }
        }

        return $this->redirectToRoute('app_login');
    }

    #[Route(path: '/mon-mot-de-passe/mettre-a-jour/{jeton}', name: 'app_reset_password', methods: ['GET', 'POST'])]
    public function reset_password(Request $request, string $jeton): Response
    {
        /** @var Requerant $requerant */
        $requerant = $this->requerantRepository->findOneBy(['jetonVerification' => $jeton]);
        if (null === $requerant) {
            return $this->redirectToRoute('app_login');
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

                    return $this->redirectToRoute('app_login');
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
