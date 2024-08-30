<?php

namespace App\Controller\Agent\Redacteur;

use App\Entity\Agent;
use App\Entity\Prejudice;
use App\Entity\Statut;
use App\Service\Breadcrumb\Breadcrumb;
use App\Service\Version\Version;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(Agent::ROLE_AGENT_REDACTEUR)]
#[Route('/agent/redacteur')]
class DefaultController extends AbstractController
{
    public function __construct(
      private Breadcrumb $breadcrumb,
      private Version $version,
      private EntityManagerInterface $em
    ) {

    }

    #[Route('/accueil', name: 'app_agent_redacteur_accueil', options: ['expose' => true])]
    public function index(): Response
    {
        $em         = $this->em;
        $statuts = $em->getRepository(Statut::class)->findBy(['code' => [
          Statut::CODE_CONSTITUE
          ]]);

        $prejudices = $em
          ->getRepository(Prejudice::class)
          ->findByStatuts($statuts,[],0,10)
        ;
        return $this->render('agent/redacteur/default/index.html.twig', [
            'prejudices' => $prejudices,
        ]);
    }
}
