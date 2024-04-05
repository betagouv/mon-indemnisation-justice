<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

class HomepageController extends AbstractController
{
    #[Route('/redirect', name: 'app_redirect')]
    public function redirection(): RedirectResponse
    {
      $user = $this->getUser();
      if($user->hasRole(User::ROLE_REQUERANT))
        return $this->redirectToRoute('app_requerant_homepage');
      return $this->redirectToRoute('app_homepage');
    }

    #[Route('/', name: 'app_homepage')]
    public function index(): Response
    {
        return $this->render('homepage/index.html.twig', [
            'controller_name' => 'HomepageController',
        ]);
    }
}
