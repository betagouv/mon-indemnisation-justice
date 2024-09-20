<?php

namespace App\Controller;

use App\Dto\ModificationMotDePasseDto;
use App\Dto\MotDePasseOublieDto;
use App\Entity\Civilite;
use App\Entity\Requerant;
use App\Repository\RequerantRepository;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
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
        protected readonly string $baseUrl,
    ) {
    }

    #[Route(path: '/mon-mot-de-passe/oublie', name: 'app_send_reset_password', methods: ['POST'], options: ['expose' => true])]
    public function motDePasseOublie(#[MapRequestPayload(acceptFormat: 'json')] MotDePasseOublieDto $motDePasseOublieDto): JsonResponse
    {
        $requerant = $this->em->getRepository(Requerant::class)->findOneBy([
            'email' => $motDePasseOublieDto->email,
            'estVerifieCourriel' => true,
        ]);

        if ($requerant && $requerant->hasRole(Requerant::ROLE_REQUERANT)) {
            // Génération d'un jeton de vérification
            $requerant->genererJetonVerification();

            $this->em->persist($requerant);
            $this->em->flush();

            // Envoi du mail de confirmation.
            $this->mailer
                ->to($requerant->getEmail())
                ->subject('Mon Indemnisation Justice: réinitialisation de votre mot de passe')
                ->htmlTemplate('email/mot_de_passe_oublie.html.twig', [
                    'requerant' => $requerant,
                ])
                ->send(pathname: 'app_reset_password', user: $requerant);
        }

        return new JsonResponse();
    }

    #[Route(path: '/mon-mot-de-passe/mettre-a-jour/{jeton}', name: 'app_reset_password', methods: ['GET', 'POST'], options: ['expose' => true])]
    public function reset_password(Request $request, RequerantRepository $ur, string $jeton): Response
    {
        /** @var Requerant $requerant */
        $requerant = $this->requerantRepository->findOneBy(['jetonVerification' => $jeton]);

        $submittedToken = $request->getPayload()->get('_csrf_token');

        $form = $this->createFormBuilder(new ModificationMotDePasseDto())
            ->add('email', TextType::class)
            ->add('motDePasse', PasswordType::class)
            ->add('confirmation', PasswordType::class)
            ->getForm();

        if ('POST' === $request->getMethod()) {
            $form->submit($request->request->all());

            if ($form->isValid()) {
                $requerant->setPassword(
                    $this->userPasswordHasher->hashPassword(
                        $requerant,
                        $request->get('_password')
                    )
                );
                $requerant->supprimerJetonVerification();

                $this->em->flush();
                $this->addFlash('success', [
                    'title' => 'Mot de passe modifié',
                    'message' => 'Le mot de passe a été modifié avec succès !',
                ]);

                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('security/reset_password.html.twig', [
            'requerant' => $requerant,
            'form' => $form,
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
            $errorMessage = 'Identifiants invalides';
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

    #[Route('/inscription/validation-du-compte/{jeton}', name: 'app_verify_email')]
    public function verifyUserEmail(string $jeton): Response
    {
        /** @var Requerant $requerant */
        $requerant = $this->requerantRepository->findOneBy(['jetonVerification' => $jeton]);
        if (null === $requerant) {
            if ($requerant->estVerifieCourriel()) {
                return $this->redirectToRoute('app_reset_password');
            }

            return $this->redirectToRoute('app_login');
        }
        $requerant->setVerifieCourriel();
        $requerant->supprimerJetonVerification();
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

        if ('GET' === $request->getMethod() && $request->getSession()->has('emailRequerantInscrit')) {
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

            /*
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
