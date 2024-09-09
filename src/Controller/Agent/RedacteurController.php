<?php

namespace App\Controller\Agent;

use App\Entity\Agent;
use App\Entity\BrisPorte;
use App\Entity\Statut;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(Agent::ROLE_AGENT_REDACTEUR)]
#[Route('/agent/redacteur')]
class RedacteurController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    #[Route('/', name: 'app_agent_redacteur_accueil', options: ['expose' => true])]
    public function index(): Response
    {
        $em = $this->em;
        $statuts = $em->getRepository(Statut::class)->findBy(['code' => [
            Statut::CODE_CONSTITUE,
        ]]);

        $brisPortes = $em
          ->getRepository(BrisPorte::class)
          ->findByStatuts($statuts, [], 0, 10)
        ;

        return $this->render('agent/redacteur/index.html.twig', [
            'prejudices' => $brisPortes,
        ]);
    }
}
