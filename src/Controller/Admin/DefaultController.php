<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Service\Breadcrumb\Breadcrumb;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(User::ROLE_ADMIN_FONC)]
#[Route('/admin')]
class DefaultController extends AbstractController
{
    public function __construct(
        private Breadcrumb $breadcrumb,
        private EntityManagerInterface $em
    ) {
    }

    #[Route('/accueil', name: 'app_admin_homepage')]
    public function index(): Response
    {
        $users = $this->em->getRepository(User::class)->findByRoles([
            User::ROLE_CHEF_PRECONTENTIEUX,
            User::ROLE_REDACTEUR_PRECONTENTIEUX,
        ]);

        $breadcrumb = $this->breadcrumb;

        return $this->render('admin/default/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'users' => $users,
        ]);
    }
}
