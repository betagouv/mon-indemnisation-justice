<?php

namespace MonIndemnisationJustice\Controller;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\Usager;
use MonIndemnisationJustice\Repository\RequerantRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class InscriptionController extends AbstractController
{
    public function __construct(
        protected EntityManagerInterface $em,
        protected readonly RequerantRepository $requerantRepository,
    ) {
    }

    #[Route('/inscription/validation-du-compte/{jeton}', name: 'app_verify_email')]
    public function verifyUserEmail(string $jeton, Request $request): Response
    {
        /** @var Usager $requerant */
        $requerant = $this->requerantRepository->findOneBy(['jetonVerification' => $jeton]);
        if (null === $requerant) {
            return $this->redirectToRoute('securite_connexion');
        }
        $requerant->setVerifieCourriel();
        $requerant->supprimerJetonVerification();
        $this->em->flush();

        $request->getSession()->getFlashBag()->add('connexion', $requerant->getEmail());

        return $this->redirectToRoute('securite_connexion');
    }
}
