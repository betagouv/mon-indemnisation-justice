<?php

namespace App\Controller\Requerant;

use App\Entity\User;
use App\Form\Requerant\RegistrationFormType;
use App\Form\Requerant\ResendPasswordFormType;
use App\Service\PasswordGenerator;
use App\Service\Mailer\Mailer;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;

class RegistrationController extends AbstractController
{

    public function __construct(
      private Mailer $mailer,
      private EmailVerifier $emailVerifier,
      private EntityManagerInterface $em,
      private UserPasswordHasherInterface $userPasswordHasher,
      private PasswordGenerator $passwordGenerator
    )
    {
    }


    #[Route('/mot-de-passe-oublie', name: 'app_resend_password_requerant')]
    public function resendPassword(Request $request): Response
    {
      $form = $this->createForm(ResendPasswordFormType::class, null);
      $form->handleRequest($request);
      if ($form->isSubmitted() && $form->isValid()) {
        $email = $form->get('email')->getData();
        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
        if($user && $user->hasRole(User::ROLE_REQUERANT)) {
          $tmpPassword = $this->passwordGenerator->new();
          /** génération d'un nouveau mot de passe et envoi */
          $user->setPassword(
              $this->userPasswordHasher->hashPassword(
                  $user,
                  $tmpPassword
              )
          );
          $this->em->flush();

          $htmlTemplate = $this->render('registration/resend_password_email.html.twig',['user' => $user,'password' => $tmpPassword]);
          $this->mailer->sendTo($user, 'Changement de mot de passe', $htmlTemplate->getContent());

          $this->addFlash(
            'notice',
            'resend_password.update_done'
          );

          return $this->redirectToRoute('app_login');
        }
      }
      return $this->render('registration/resend_password.html.twig', [
          'resendPasswordForm' => $form,
      ]);
    }

    #[Route('/creer-un-compte', name: 'app_add_requerant')]
    public function register(Request $request): Response
    {
        $userPasswordHasher = $this->userPasswordHasher;
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        $entityManager=$this->em;
        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $user->addRole(User::ROLE_REQUERANT);
            $entityManager->persist($user);
            $entityManager->flush();

            // generate a signed url and email it to the user
            $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                (new TemplatedEmail())
                    ->from(new Address('dot-nepasrepondre@justice.gouv.fr', 'precontentieux'))
                    ->to($user->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/validation-du-compte-requerant', name: 'app_verify_email')]
    public function verifyUserEmail(Request $request, TranslatorInterface $translator): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $translator->trans($exception->getReason(), [], 'VerifyEmailBundle'));

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_login');
    }
}
