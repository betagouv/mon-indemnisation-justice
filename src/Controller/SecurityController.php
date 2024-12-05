<?php

namespace App\Controller;

use App\Dto\ModificationMotDePasse;
use App\Dto\MotDePasseOublieDto;
use App\Entity\Requerant;
use App\Forms\ModificationMotDePasseType;
use App\Repository\RequerantRepository;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
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

    #[Route(path: '/mon-mot-de-passe/oublie', name: 'app_send_reset_password', methods: ['POST'])]
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
                ->toRequerant($requerant)
                ->subject('Mon Indemnisation Justice: réinitialisation de votre mot de passe')
                ->htmlTemplate('email/mot_de_passe_oublie.html.twig', [
                    'requerant' => $requerant,
                ])
                ->send();
        }

        return new JsonResponse();
    }

    #[Route(path: '/mon-mot-de-passe/mettre-a-jour/{jeton}', name: 'app_reset_password', methods: ['GET', 'POST'])]
    public function reset_password(Request $request, RequerantRepository $ur, string $jeton): Response
    {
        /** @var Requerant $requerant */
        $requerant = $this->requerantRepository->findOneBy(['jetonVerification' => $jeton]);
        if (null === $requerant) {
            return $this->redirectToRoute('app_login');
        }

        $modificationMotDePasse = new ModificationMotDePasse();

        $form = $this->createForm(ModificationMotDePasseType::class, $modificationMotDePasse);
        $errors = [];

        if ($request->getMethod() === Request::METHOD_POST) {
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

    #[Route(path: '/connexion', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(Request $request): Response
    {
        $error = $this->authenticationUtils->getLastAuthenticationError();
        $lastUsername = $request->query->get('courriel') ?? $this->authenticationUtils->getLastUsername();
        $user = $this->getUser();

        $errorMessage = '';
        if ($error && $error->getMessage()) {
            $errorMessage = 'Identifiants invalides';
        }
        if (null !== $user) {
            return $this->redirectToRoute('requerant_home_index');
        }

        return $this->render('security/connexion.html.twig', [
            'last_username' => $lastUsername,
            'error_message' => $errorMessage,
        ]);
    }

    #[Route(path: '/deconnexion', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
