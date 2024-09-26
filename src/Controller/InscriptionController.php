<?php

namespace App\Controller;

use App\Dto\Inscription;
use App\Entity\Requerant;
use App\Forms\InscriptionType;
use App\Repository\RequerantRepository;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class InscriptionController extends AbstractController
{
    public function __construct(
        protected UserPasswordHasherInterface $userPasswordHasher,
        protected AuthenticationUtils $authenticationUtils,
        protected Mailer $mailer,
        protected EntityManagerInterface $em,
        protected readonly RequerantRepository $requerantRepository,
    ) {
    }

    #[Route(path: '/inscription', name: 'app_inscription', methods: ['GET', 'POST'])]
    public function inscription(Request $request): Response
    {
        $user = $this->getUser();
        if (null !== $user && $user instanceof Requerant) {
            return $this->redirectToRoute('requerant_home_index');
        }

        // TODO: remplacer par un message flash https://symfony.com/doc/current/session.html#flash-messages
        if ('GET' === $request->getMethod() && $request->getSession()->has('emailRequerantInscrit')) {
            $email = $request->getSession()->get('emailRequerantInscrit');
            $request->getSession()->remove('emailRequerantInscrit');

            return $this->render('security/success.html.twig', [
                'email' => $email,
            ]);
        }

        $inscription = new Inscription();
        $form = $this->createForm(InscriptionType::class, $inscription);
        $errors = [];

        if (Request::METHOD_POST === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isSubmitted()) {
                /** @var Inscription $inscription */
                $inscription = $form->getData();
                if ($form->isValid()) {
                    // Création du compte requérant
                    $requerant = (new Requerant())
                        ->setEmail($inscription->courriel)
                    ;
                    $requerant->getPersonnePhysique()
                        ->setCivilite($inscription->civilite)
                        ->setPrenom1($inscription->prenom)
                        ->setNom($inscription->nom)
                        ->setNomNaissance($inscription->nomNaissance)
                    ;
                    $requerant->setPassword(
                        $this->userPasswordHasher->hashPassword(
                            $requerant,
                            $inscription->motDePasse
                        )
                    );
                    // $requerant->addRole(Requerant::ROLE_REQUERANT);
                    $requerant->genererJetonVerification();
                    $requerant->setTestEligibilite($request->getSession()->get('testEligibilite'));
                    $request->getSession()->remove('testEligibilite');
                    $this->em->persist($requerant);
                    $this->em->flush();

                    // Envoi du mail de confirmation.
                    $this->mailer
                        ->toRequerant($requerant)
                        ->subject("Activation de votre compte sur l'application Mon indemnisation justice")
                        ->htmlTemplate('email/inscription_a_finaliser.html.twig', [
                            'requerant' => $requerant,
                        ])
                        ->send();
                    // Ajout d'un drapeau pour marquer la réussite de l'inscription et pouvoir rediriger vers une page de succès
                    $request->getSession()->set('emailRequerantInscrit', $requerant->getEmail());

                    return $this->redirectToRoute('app_inscription');
                } else {
                    /** @var FormError $error */
                    foreach ($form->getErrors(true) as $error) {
                        $errors[$error->getOrigin()?->getName()] = $error->getMessage();
                    }
                }
            }
        }

        return $this->render('security/inscription.html.twig', [
            'inscription' => $inscription,
            'form' => $form,
            'errors' => $errors,
        ]);
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
}
