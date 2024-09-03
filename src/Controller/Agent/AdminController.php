<?php

namespace App\Controller\Agent;

use App\Entity\Agent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(Agent::ROLE_AGENT_GESTION_PERSONNEL)]
#[Route('/agent/gestion')]
class AdminController extends AbstractController
{
    public function __construct(
        protected readonly EntityManagerInterface $em
    ) {
    }

    #[Route('/', name: 'app_admin_homepage')]
    public function index(): Response
    {
        $agents = $this->em->getRepository(Agent::class)->findByRoles([
            Agent::ROLE_AGENT_VALIDATEUR,
            Agent::ROLE_AGENT_REDACTEUR
        ]);

        return $this->render('admin/default/index.html.twig', [
            'agents' => $agents
        ]);
    }
}
