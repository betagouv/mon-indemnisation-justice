<?php

declare(strict_types=1);

namespace App\Controller;

use App\Dto\Inscription;
use App\Entity\BrisPorte;
use App\Entity\GeoDepartement;
use App\Entity\Requerant;
use App\Entity\TestEligibilite;
use App\Forms\InscriptionType;
use App\Forms\TestEligibiliteType;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;


#[Route('/bris-de-porte')]
class BrisPorteController extends AbstractController
{
    public const SESSION_CONTEXT_KEY = 'testEligibilite';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $userPasswordHasher,
        private readonly Mailer $mailer,
    ) {
    }

    protected function getTestEligibilite(Request $request): ?TestEligibilite
    {
        $id = $request->getSession()->get(self::SESSION_CONTEXT_KEY, null);

        if ($id) {
            try {
                return $this->entityManager->find(TestEligibilite::class, $id);
            } catch (ORMException $exception) {
                $request->getSession()->remove(self::SESSION_CONTEXT_KEY);
            }

        }

        return null;
    }

    protected function setTestEligibilite(TestEligibilite $testEligibilite, Request $request): void
    {
        $request->getSession()->set(self::SESSION_CONTEXT_KEY, $testEligibilite?->id);
    }

    #[Route('/tester-mon-eligibilite', name: 'bris_porte_tester_eligibilite', methods: ['GET', 'POST'])]
    public function testerMonEligibilite(Request $request): Response
    {
        $testEligibilite = $this->getTestEligibilite($request) ?? new TestEligibilite();

        if ($request->getSession()->has(AtterrissageController::SESSION_KEY)) {
            $testEligibilite->estIssuAttestation = true;

            $request->getSession()->remove(AtterrissageController::SESSION_KEY);
        }

        $form = $this->createForm(TestEligibiliteType::class, $testEligibilite);

        if (Request::METHOD_POST === $request->getMethod()) {

            $form->handleRequest($request);
            if ($form->isSubmitted() && $form->isValid()) {
                /** @var TestEligibilite $testEligibilite */
                $testEligibilite = $form->getData();

                $testEligibilite->estEligibleExperimentation = $testEligibilite->departement->estDeploye();
                $testEligibilite->dateSoumission = new \DateTime();

                $this->entityManager->persist($testEligibilite);
                $this->entityManager->flush();

                if (($requerant = $this->getUser()) instanceof Requerant) {
                    $dossier = (new BrisPorte())
                        ->setRequerant($requerant)
                        ->setTestEligibilite($testEligibilite);

                    $this->entityManager->persist($dossier);
                    $this->entityManager->flush();

                    return $this->redirectToRoute('app_bris_porte_edit', ['id' => $dossier->getId()]);
                }

                if (null !== $testEligibilite->requerant) {
                    return $this->redirectToRoute('bris_porte_finaliser_la_creation');
                }

                $this->setTestEligibilite($testEligibilite, $request);

                if ($testEligibilite->departement->estDeploye()) {
                    return $this->redirectToRoute('bris_porte_creation_de_compte');
                }

                return $this->redirectToRoute('bris_porte_contactez_nous');
            }
        }

        return $this->render('bris_porte/tester_mon_eligibilite.html.twig', [
            'form' => $form,
            'departements' => $this->entityManager->getRepository(GeoDepartement::class)->getListeTriee()]
        );
    }

    #[Route('/contactez-nous', name: 'bris_porte_contactez_nous', methods: ['GET'])]
    public function contactezNous(Request $request): Response
    {
        $testEligibilite = $this->getTestEligibilite($request);

        if (null === $testEligibilite) {
            return $this->redirectToRoute('bris_porte_tester_eligibilite');
        }

        if (null !== $testEligibilite->requerant) {
            return $this->redirectToRoute('bris_porte_finaliser_la_creation');
        }

        if ($testEligibilite->departement->estDeploye()) {
            return $this->redirectToRoute('bris_porte_creation_de_compte');
        }

        return $this->render('bris_porte/contactez_nous.html.twig', [
            'testEligibilite' => $testEligibilite,
        ]);
    }

    #[Route(path: '/creation-de-compte', name: 'bris_porte_creation_de_compte', methods: ['GET', 'POST'])]
    public function creationDeCompte(Request $request): Response
    {
        if ($this->getUser() instanceof Requerant) {
            return $this->redirectToRoute('requerant_home_index');
        }

        $testEligibilite = $this->getTestEligibilite($request);

        if (null === $testEligibilite) {
            return $this->redirectToRoute('bris_porte_tester_eligibilite');
        }

        if (null !== $testEligibilite->requerant) {
            return $this->redirectToRoute('bris_porte_finaliser_la_creation');
        }

        if (!$testEligibilite->departement->estDeploye()) {
            return $this->redirectToRoute('bris_porte_contactez_nous');
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
                        ->setEmail($inscription->courriel)
                        ->setTelephone($inscription->telephone)
                        ->setNom($inscription->nom)
                        ->setNomNaissance($inscription->nomNaissance ?? $inscription->nom)
                    ;
                    $requerant->setPassword(
                        $this->userPasswordHasher->hashPassword(
                            $requerant,
                            $inscription->motDePasse
                        )
                    );
                    $requerant->genererJetonVerification();

                    $this->entityManager->persist($requerant);

                    $dossier = (new BrisPorte())
                        ->setRequerant($requerant)
                        ->setTestEligibilite($testEligibilite)
                    ;
                    $this->entityManager->persist($dossier);

                    $testEligibilite->requerant = $requerant;
                    $this->entityManager->persist($testEligibilite);

                    $this->entityManager->flush();
                    $this->setTestEligibilite($testEligibilite, $request);

                    // Envoi du mail de confirmation.
                    $this->mailer
                        ->toRequerant($requerant)
                        ->subject("Activation de votre compte sur l'application Mon Indemnisation Justice")
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
        $testEligibilite = $this->getTestEligibilite($request);

        if (null === $testEligibilite) {
            return $this->redirectToRoute('bris_porte_tester_eligibilite');
        } else {
            if (!$testEligibilite->departement->estDeploye()) {
                return $this->redirectToRoute('bris_porte_contactez_nous');
            }

            if (null === $testEligibilite->requerant) {
                return $this->redirectToRoute('bris_porte_creation_de_compte');
            }
        }

        return $this->render('bris_porte/finaliser_la_creation.html.twig', ['email' => $testEligibilite->requerant->getEmail()]);
    }
}
