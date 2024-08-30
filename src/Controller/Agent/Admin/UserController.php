<?php

namespace App\Controller\Agent\Admin;

use App\Entity\Agent;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/agent')]
class UserController extends AbstractController
{
    #[IsGranted(Agent::ROLE_AGENT_GESTION_PERSONNEL)]
    #[Route('/administration/utilisateur/', name: 'app_admin_user_list')]
    public function index(): Response
    {
        return $this->render('admin/user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }
}
