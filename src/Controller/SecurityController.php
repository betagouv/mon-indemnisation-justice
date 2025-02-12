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
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
        protected readonly OidcClient $oidcClient,
    ) {
    }

    #[Route(path: '/connexion', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(Request $request): Response
    {
        $error = $this->authenticationUtils->getLastAuthenticationError();
        $lastUsername = $request->query->get('courriel') ?? $this->authenticationUtils->getLastUsername();

        $errorMessage = '';
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
            'last_username' => $lastUsername,
            'error_message' => $errorMessage,
            'mdp_oublie_form' => $this->createForm(MotDePasseOublieType::class, new MotDePasseOublieDto()),
        ]);
    }

    #[Route(path: '/connexion-agent', name: 'securite_connexion_agent', methods: ['POST'])]
    public function connexionAgent(Request $request): Response
    {
        if ($this->getUser() instanceof Agent) {
            return $this->redirectToRoute('agent_index');
        }

        if ($this->isCsrfTokenValid('connexionAgent', $request->getPayload()->get('_csrf_token_agent'))) {
            return $this->redirect($this->oidcClient->buildAuthorizeUrl($request));
        }

        return $this->render('security/connexion.html.twig', [
            'last_username' => null,
            'error_message' => null,
            'mdp_oublie_form' => $this->createForm(MotDePasseOublieType::class, new MotDePasseOublieDto()),
        ]);
    }

    #[Route(path: '/deconnexion', name: 'app_logout')]
    public function logout(): Response
    {
        return $this->redirectToRoute('requerant_home_index');
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
                        ->send();
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
                } else {
                    /** @var FormError $error */
                    foreach ($form->getErrors(true) as $key => $error) {
                        $errors[$error->getOrigin()?->getName()] = $error->getMessage();
                    }
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
