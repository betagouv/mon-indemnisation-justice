<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\Inscription;
use App\Dto\TestEligibilite;
use App\Entity\Requerant;
use App\Forms\InscriptionType;
use App\Forms\TestEligibiliteType;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/bris-de-porte')]
class BrisPorteController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly Mailer $mailer,
    ) {
    }

    #[Route('', name: 'bris_porte_atterrissage')]
    public function atterrissage(): Response
    {
        return $this->render('bris_porte/atterrissage.html.twig');
    }

    #[Route('/tester-mon-eligibilite', name: 'bris_porte_tester_eligibilite', methods: ['GET', 'POST'])]
    public function testerMonEligibilite(Request $request): Response
    {
        $testEligibilite = new TestEligibilite();
        $form = $this->createForm(TestEligibiliteType::class, $testEligibilite);

        if (Request::METHOD_POST === $request->getMethod()) {
            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                /** @var TestEligibilite $testEligibilite */
                $testEligibilite = $form->getData();

                $requerant = $this->getUser();
                if ($requerant instanceof Requerant) {
                    $requerant->setTestEligibilite($testEligibilite->toArray());
                    $this->entityManager->persist($requerant);
                    $this->entityManager->flush();

                    return $this->redirectToRoute('app_bris_porte_add');
                } else {
                    $request->getSession()->set('testEligibilite', $testEligibilite->toArray());

                    return $this->redirectToRoute('bris_porte_creation_de_compte');
                }
            }
        }

        return $this->render('bris_porte/tester_mon_eligibilite.html.twig', ['form' => $form]);
    }

    #[Route(path: '/creation-de-compte', name: 'bris_porte_creation_de_compte', methods: ['GET', 'POST'])]
    public function inscription(Request $request): Response
    {
        $user = $this->getUser();
        if ($user instanceof Requerant) {
            return $this->redirectToRoute('requerant_home_index');
        }

        if (!$request->getSession()->has('testEligibilite')) {
            return $this->redirectToRoute('bris_porte_tester_eligibilite');
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
                    $this->entityManager->persist($requerant);
                    $this->entityManager->flush();

                    // Envoi du mail de confirmation.
                    $this->mailer
                        ->toRequerant($requerant)
                        ->subject("Activation de votre compte sur l'application Mon indemnisation justice")
                        ->htmlTemplate('email/inscription_a_finaliser.html.twig', [
                            'requerant' => $requerant,
                        ])
                        ->send();
                    // Ajout d'un drapeau pour marquer la réussite de l'inscription et pouvoir rediriger vers une page de succès
                    $this->addFlash('emailRequerantInscrit', $requerant->getEmail());

                    return $this->redirectToRoute('bris_porte_finaliser_la_creation');
                } else {
                    /** @var FormError $error */
                    foreach ($form->getErrors(true) as $error) {
                        $errors[$error->getOrigin()?->getName()] = $error->getMessage();
                    }
                }
            }
        }

        return $this->render('bris_porte/creation_de_compte.html.twig', [
            'inscription' => $inscription,
            'form' => $form,
            'errors' => $errors,
        ]);
    }

    #[Route(path: '/finaliser-la-creation', name: 'bris_porte_finaliser_la_creation')]
    public function finaliserLaCreation(Request $request): Response
    {
        $email = @$request->getSession()->getFlashBag()->get('emailRequerantInscrit')[0];

        if (!$email) {
            return $this->redirectToRoute('bris_porte_atterrissage');
        }

        return $this->render('bris_porte/finaliser_la_creation.html.twig', ['email' => $email]);
    }
}
