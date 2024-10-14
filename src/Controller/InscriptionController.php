<?php

namespace App\Controller;

use App\Entity\Requerant;
use App\Repository\RequerantRepository;
use App\Service\Mailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

    #[Route('/inscription/validation-du-compte/{jeton}', name: 'app_verify_email')]
    public function verifyUserEmail(string $jeton): Response
    {
        /** @var Requerant $requerant */
        $requerant = $this->requerantRepository->findOneBy(['jetonVerification' => $jeton]);
        if (null === $requerant) {
            return $this->redirectToRoute('app_login');
        }
        $requerant->setVerifieCourriel();
        $requerant->supprimerJetonVerification();
        $this->em->flush();

        return $this->redirectToRoute('app_login', ['courriel' => $requerant->getEmail()]);
    }
}
