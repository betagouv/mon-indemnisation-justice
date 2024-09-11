<?php

namespace App\Controller\Agent;

use App\Entity\Agent;
use App\Entity\BrisPorte;
use App\Entity\EtatBrisPorte;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(Agent::ROLE_AGENT_VALIDATEUR)]
#[Route('/agent/validation')]
class ValidationController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em
    ) {
    }

    #[Route('/accueil', name: 'app_chef_precontentieux_homepage', options: ['expose' => true])]
    public function index(): Response
    {
        $statuts = $this->em->getRepository(EtatBrisPorte::class)->findBy(['code' => [
            EtatBrisPorte::CODE_VALIDE, EtatBrisPorte::CODE_REJETE,
        ]]);

        $brisPortes = $this->em
          ->getRepository(BrisPorte::class)
          ->findByStatuts($statuts, [], 0, 10)
        ;

        return $this->render('chef_precontentieux/default/index.html.twig', [
            'prejudices' => $brisPortes,
        ]);
    }
}
