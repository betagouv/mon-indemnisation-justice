<?php

namespace App\Controller;

use App\Entity\Tracking;
use App\Entity\Civilite;
use App\Entity\User;
use App\Security\EmailVerifier;
use App\Service\Breadcrumb\Breadcrumb;
use App\Service\Mailer\Mailer;
use App\Service\Version\Version;
use Doctrine\ORM\EntityManagerInterface;
use FOPG\Component\UtilsBundle\Env\Env;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Contracts\Translation\TranslatorInterface;

class SecurityController extends AbstractController
{
    public function __construct(
      private UrlGeneratorInterface $urlGenerator,
      private TranslatorInterface $translator,
      private UserPasswordHasherInterface $userPasswordHasher,
      private Breadcrumb $breadcrumb,
      private AuthenticationUtils $authenticationUtils,
      private Version $version,
      private EntityManagerInterface $em,
      private EmailVerifier $emailVerifier
    ) {

    }

    #[Route(path: '/envoi-de-mon-mot-de-passe', name: 'app_send_reset_password', methods: ['POST'], options: ['expose' => true])]
    public function send_email_for_reset(Request $request): JsonResponse
    {
      $content = json_decode($request->getContent(),true);
      $email = $content['email']??null;
      $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
      $sent = false;
      if($user && $user->hasRole(User::ROLE_REQUERANT)) {
        $user->generateExpirationLink();
        $this->em->flush();

        $url = $this->urlGenerator->generate(
          'app_reset_password',
          ['id'=> $user->getId(),'expirationLink' => $user->getExpirationLink()],
          UrlGeneratorInterface::ABSOLUTE_URL
        );

        $appName = $this->translator->trans('header.name');
        $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
          (new TemplatedEmail())
            ->from(new Address(Env::get('EMAIL_FROM'), Env::get('EMAIL_FROM_LABEL')))
            ->to($user->getEmail())
            ->subject(str_replace(["%name%"],[$appName],$this->translator->trans('security.reset_password.email.title')))
            ->htmlTemplate('security/send_link_for_new_password.html.twig')
            ->context([
              'name' => $appName,
              'mail' => $user->getEmail(),
              'url' => $url,
              'nomComplet' => $user->getNomComplet(),
          ])
        );
        $sent=true;
      }
      return new JsonResponse(['email' => $email,'sent' => $sent]);
    }

    #[Route(path: '/{id}/je-mets-a-jour-mon-mot-de-passe/{expirationLink}', name: 'app_reset_password', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function reset_password(?User $user, Request $request): Response
    {
      $breadcrumb = $this->breadcrumb;
      $breadcrumb->add('homepage.title','app_homepage');
      $breadcrumb->add('security.reset_password.title');

      $submittedToken = $request->getPayload()->get('_csrf_token');
      $successMsg="";
      if($user && $user->checkExpirationLink($request->get('expirationLink'))) {
        /**
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
          $user->setExpirationLink(null);
          $user->setExpirationDatetime(null);
          $successMsg = $this->translator->trans('security.reset_password.success.password_reseted');
          $this->em->flush();
        }
        return $this->render('security/reset_password.html.twig',[
          'user' => $user,
          'successMsg' => $successMsg,
          'breadcrumb' => $breadcrumb,
          'version' => $this->version,
        ]);
      }
      throw $this->createNotFoundException($this->translator->trans('security.reset_password.error.failed'));
    }

    #[Route(path: '/connexion', name: 'app_login', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function login(): Response
    {
        $error = $this->authenticationUtils->getLastAuthenticationError();
        $lastUsername = $this->authenticationUtils->getLastUsername();
        $breadcrumb = $this->breadcrumb;
        $breadcrumb->add('homepage.title','app_homepage');
        $breadcrumb->add('login.title');
        $user = $this->getUser();
        $errorMessage="";
        if($error && $error->getMessage())
          $errorMessage = $this->translator->trans($error->getMessage(),[],'security');
        if(null !== $user)
          return $this->redirect('/redirect');

        return $this->render('security/connexion.html.twig', [
          'last_username' => $lastUsername,
          'error_message' => $errorMessage,
          'breadcrumb' => $breadcrumb,
          'version' => $this->version,
        ]);
    }

    #[Route(path: '/deconnexion', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    private static function has_fields(Request $request): bool
    {
      return (null !== $request->get('type',null));
    }

    private static function get_fields(Request $request): array
    {
      $type = $request->get('type',null);
      $fields=['type' => $type];
      switch($type) {
        case 'BRI':
          $fields=array_merge($fields,[
            'dateOperationPJ' => null,
            'numeroPV' => null,
            'numeroParquet' => null,
            'isErreurPorte' => null
          ]);
          foreach($fields as $field => $value)
            $fields[$field]=$request->get($field);
          break;
        default:
      }
      return $fields;
    }

    private function handleUserRequest(Request $request, User $user): void
    {
          /** @var ?int $civiliteId */
          $civiliteId = $request->get('civilite',null);
          /** @var ?Civilite $civilite */
          $civilite = (null !== $civiliteId) ? $this->em->getRepository(Civilite::class)->find($civiliteId) : null;
          $userPasswordHasher = $this->userPasswordHasher;
          $user->setEmail($request->get('email'));
          $user->getPersonnePhysique()->setPrenom1($request->get('prenom1'));
          $user->getPersonnePhysique()->setNom($request->get('nom'));
          $user->getPersonnePhysique()->setNomNaissance($request->get('nomNaissance'));
          $user->setPassword(
              $userPasswordHasher->hashPassword(
                  $user,
                  $request->get('password')
              )
          );
          $user->getPersonnePhysique()->setCivilite($civilite);
          $user->addRole(User::ROLE_REQUERANT);
    }

    #[Route(path: '/inscription', name: 'app_inscription', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function inscription(Request $request): Response
    {
        $user = $this->getUser();
        if(null !== $user)
          return $this->redirect('/redirect');

        $breadcrumb = $this->breadcrumb;
        $breadcrumb->add('homepage.title','app_homepage');
        $session = $request->getSession();
        $submittedToken = $request->getPayload()->get('_csrf_token');
        $fields = self::get_fields($request);
        if($fields['type'] === 'BRI')
          $breadcrumb->add('bris_porte.test_eligibilite.title','app_bris_porte_test_eligibilite');

        /**
         * Le formulaire a bien été soumis
         *
         */
        if ($this->isCsrfTokenValid('authenticate', $submittedToken)) {
          /**
           * Création du nouveau compte
           *
           */
          $user = new User();
          $this->handleUserRequest($request, $user);
          $this->em->persist($user);
          $this->em->flush();

          $urlTracking = $this->urlGenerator->generate(
            'app_tracking',
            ['id'=> $user->getId(),'md5' => md5($user->getEmail())],
            UrlGeneratorInterface::ABSOLUTE_URL
          );
          /**
           * Envoi du mail de confirmation
           *
           */
          $appName = $this->translator->trans('header.name');
          $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
            (new TemplatedEmail())
              ->from(new Address(Env::get('EMAIL_FROM'), Env::get('EMAIL_FROM_LABEL')))
              ->to($user->getEmail())
              ->subject(str_replace(["%name%"],[$appName],$this->translator->trans('register.email.title')))
              ->htmlTemplate('registration/confirmation_email.html.twig')
              ->context([
                'mail' => $user->getEmail(),
                'url' => Env::get('BASE_URL'),
                'nomComplet' => $user->getNomComplet(),
                'urlTracking' => $urlTracking,
              ])
          );
          $this->em->getRepository(Tracking::class)->add($user,Tracking::EVENT_SEND_EMAIL_CREATE_ACCOUNT);

          $breadcrumb->add('security.success.title',null);

          return $this->render('security/success.html.twig', [
            'user' => $user,
            'breadcrumb' => $this->breadcrumb,
            'version' => $this->version
          ]);
        }
        /**
         * Le formulaire a bien été identifié
         *
         */
        elseif(true === self::has_fields($request)) {
          if($fields['type'] === 'BRI') { }
          else {
            $session->set("test_eligibilite",null);
            return $this->redirect('/');
          }
          $breadcrumb->add('security.inscription.title',null);
          $session->set("test_eligibilite",$fields);
        }

        $error = $this->authenticationUtils->getLastAuthenticationError();
        $lastUsername = $this->authenticationUtils->getLastUsername();
        return $this->render('security/inscription.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'breadcrumb' => $this->breadcrumb,
            'version' => $this->version
        ]);
    }
}
