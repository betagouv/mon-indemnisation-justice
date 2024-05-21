<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\Version\Version;
use App\Service\Breadcrumb\Breadcrumb;
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
      if($user->hasRole(User::ROLE_ADMIN_FONC))
        return $this->redirectToRoute('app_admin_homepage');
      if($user->hasRole(User::ROLE_REDACTEUR_PRECONTENTIEUX))
        return $this->redirectToRoute('app_redacteur_homepage');
      return $this->redirectToRoute('app_homepage');
    }

    #[Route('/', name: 'app_homepage')]
    public function index(Breadcrumb $breadcrumb, Version $version): Response
    {
        $breadcrumb->add("homepage.title", null);
        $user = $this->getUser();
        if($user && $user->hasRole(User::ROLE_REQUERANT))
          $breadcrumb->add('requerant.homepage.title', 'app_requerant_homepage');
        return $this->render('homepage/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'version' => $version,
        ]);
    }

    #[Route('/conditions-generales-d-utilisation', name: 'app_cgu',options: ['expose' => true])]
    public function cgu(Breadcrumb $breadcrumb, Version $version): Response
    {
        return $this->render('homepage/cgu.html.twig', [
            'breadcrumb' => $breadcrumb,
            'version' => $version,
        ]);
    }
}
