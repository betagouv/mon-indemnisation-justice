<?php

namespace App\Controller;

use App\Entity\Civilite;
use App\Entity\Requerant;
use App\Repository\RequerantRepository;
use App\Service\Mailer\BasicMailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
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
        protected BasicMailer $mailer,
        protected EntityManagerInterface $em,
        protected readonly RequerantRepository $requerantRepository,
        protected readonly string $baseUrl
    ) {
    }

    #[Route(path: '/envoi-de-mon-mot-de-passe', name: 'app_send_reset_password', methods: ['POST'], options: ['expose' => true])]
    public function send_email_for_reset(Request $request): JsonResponse
    {
        $content = json_decode($request->getContent(), true);
        $email = $content['email'] ?? null;
        $user = $this->em->getRepository(Requerant::class)->findOneBy(['email' => $email]);
        $sent = false;
        if ($user && $user->hasRole(Requerant::ROLE_REQUERANT)) {
            // Envoi du mail de confirmation.
            $this->mailer
                ->to($user->getEmail())
                ->subject("Mon Indemnisation Justice: réinitialisation de votre mot de passe")
                ->htmlTemplate('security/send_link_for_new_password.html.twig', [
                    'name' => "Mon Indemnisation Justice",
                    'mail' => $user->getEmail(),
                    'url' => $this->baseUrl,
                    'nomComplet' => $user->getNomComplet(),
                ])
                ->send(pathname: 'app_reset_password', user: $user);
            $sent = true;
        }

        return new JsonResponse(['email' => $email, 'sent' => $sent]);
    }

    #[Route(path: '/je-mets-a-jour-mon-mot-de-passe', name: 'app_reset_password', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function reset_password(Request $request, RequerantRepository $ur): Response
    {
        /** @var ?int $id */
        $id = $request->query->get('id', null);
        /** @var ?Requerant $user */
        $user = $ur->find($id);
        if (
            (null === $user)
            || (false === $this->mailer->check($request, $user))
        ) {
            throw $this->createNotFoundException("Le lien n'est pas valide ou expiré. Veuillez renouveler une nouvelle tentative de réinitialisation de votre mot de passe");
        }

        $submittedToken = $request->getPayload()->get('_csrf_token');
        $successMsg = '';
        /*
         * Le formulaire a bien été soumis
         *
         */
        if ($this->isCsrfTokenValid('authenticate', $submittedToken)) {
            $userPasswordHasher = $this->userPasswordHasher;
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $request->get('_password')
                )
            );
            $successMsg = 'Le mot de passe a été mis à jour avec succès !';
            $this->em->flush();
        }

        return $this->render('security/reset_password.html.twig', [
            'user' => $user,
            'successMsg' => $successMsg,
        ]);
    }

    #[Route(path: '/connexion', name: 'app_login', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function login(Request $request): Response
    {
        $error = $this->authenticationUtils->getLastAuthenticationError();
        $lastUsername = $request->query->get('courriel') ?? $this->authenticationUtils->getLastUsername();
        $user = $this->getUser();
        $isAgent = ('1' == $request->get('isAgent'));

        $errorMessage = '';
        if ($error && $error->getMessage()) {
            $errorMessage = "Identifiants invalides";
        }
        if (null !== $user) {
            return $this->redirect('/redirect');
        }

        return $this->render('security/connexion.html.twig', [
            'last_username' => $lastUsername,
            'error_message' => $errorMessage,
            'is_agent' => $isAgent,
        ]);
    }

    #[Route(path: '/deconnexion', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    private static function has_fields(Request $request): bool
    {
        return null !== $request->get('type', null);
    }

    private static function get_fields(Request $request): array
    {
        $type = $request->get('type', null);
        $fields = ['type' => $type];
        switch ($type) {
            case 'BRI':
                $fields = array_merge($fields, [
                    'dateOperationPJ' => null,
                    'numeroPV' => null,
                    'numeroParquet' => null,
                    'isErreurPorte' => null,
                ]);
                foreach ($fields as $field => $value) {
                    $fields[$field] = $request->get($field);
                }
                break;
            default:
        }

        return $fields;
    }

    #[Route('/validation-du-compte-requerant/{jeton}', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, string $jeton): Response
    {
        $requerant = $this->requerantRepository->findOneBy(['jetonVerification' => $jeton]);
        if (null === $requerant) {
            return $this->redirectToRoute('app_login');
        }
        $requerant->setVerifieCourriel();
        $this->em->flush();

        return $this->redirectToRoute('app_login', ['courriel' => $requerant->getEmail()]);
    }

    #[Route(path: '/inscription', name: 'app_inscription', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function inscription(Request $request): Response
    {
        $user = $this->getUser();
        if (null !== $user && $user instanceof Requerant) {
            return $this->redirectToRoute('requerant_home_index');
        }

        if ($request->getMethod() === 'GET' && $request->getSession()->has('emailRequerantInscrit')) {
            $email = $request->getSession()->get('emailRequerantInscrit');
            $request->getSession()->remove('emailRequerantInscrit');

            return $this->render('security/success.html.twig', [
                'email' => $email,
            ]);
        }

        /*
         * TODO: utiliser un **VRAI** formulaire Symfony
         *
         * Le formulaire a bien été soumis
         */
        if ($this->isCsrfTokenValid('authenticate', $request->getPayload()->get('_csrf_token'))) {
            /**
             * Création du nouveau compte.
             */
            $requerant = new Requerant();
            $requerant->setEmail($request->get('email'));
            $requerant->getPersonnePhysique()->setCivilite(Civilite::tryFrom($request->get('civilite')));
            $requerant->getPersonnePhysique()->setPrenom1($request->get('prenom1'));
            $requerant->getPersonnePhysique()->setNom($request->get('nom'));
            $requerant->getPersonnePhysique()->setNomNaissance($request->get('nomNaissance'));
            $requerant->setPassword(
            $this->userPasswordHasher->hashPassword(
                $requerant,
                $request->get('password')
                )
            );
            $requerant->addRole(Requerant::ROLE_REQUERANT);
            $requerant->genererJetonVerification();
            $requerant->setTestEligibilite($request->getSession()->get('testEligibilite'));
            $request->getSession()->remove('testEligibilite');
            $this->em->persist($requerant);
            $this->em->flush();

            /**
             * Envoi du mail de confirmation.
             */
            $this->mailer
                ->to($requerant->getEmail())
                ->subject("Activation de votre compte sur l'application Mon indemnisation justice")
                ->htmlTemplate('email/inscription_a_finaliser.html.twig', [
                    'requerant' => $requerant,
                ])
                ->send($requerant);
            // Ajout d'un drapeau pour marquer la réussite de l'inscription:
            $request->getSession()->set('emailRequerantInscrit', $requerant->getEmail());

            return $this->redirectToRoute('app_inscription');
        }

        $error = $this->authenticationUtils->getLastAuthenticationError();
        $lastUsername = $this->authenticationUtils->getLastUsername();

        return $this->render('security/inscription.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }
}
