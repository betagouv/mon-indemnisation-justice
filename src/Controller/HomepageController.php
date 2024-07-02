<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Prejudice;
use App\Service\Version\Version;
use App\Service\Breadcrumb\Breadcrumb;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

class HomepageController extends AbstractController
{
    public function __construct(
      private EntityManagerInterface $em
    )
    { }

    #[Route('/redirect', name: 'app_redirect', options: ['expose' => true])]
    public function redirection(): RedirectResponse
    {
      $user = $this->getUser();
      if($user->hasRole(User::ROLE_REQUERANT))
        return $this->redirectToRoute('app_requerant_homepage');
      if($user->hasRole(User::ROLE_ADMIN_FONC))
        return $this->redirectToRoute('app_admin_homepage');
      if($user->hasRole(User::ROLE_REDACTEUR_PRECONTENTIEUX))
        return $this->redirectToRoute('app_redacteur_homepage');
      if($user->hasRole(User::ROLE_CHEF_PRECONTENTIEUX))
        return $this->redirectToRoute('app_chef_precontentieux_homepage');
      return $this->redirectToRoute('app_homepage');
    }

    #[Route('/', name: 'app_homepage',options: ['expose' => true])]
    #[Route('/declarer-un-prejudice', name: 'app_category', options: ['expose' => true])]
    public function category(Breadcrumb $breadcrumb, Version $version): Response
    {
      $breadcrumb->add("prejudice.title", null);
      $user = $this->getUser();
      if($user && $user->hasRole(User::ROLE_REQUERANT))
        $breadcrumb->add('requerant.homepage.title', 'app_requerant_homepage');
      return $this->render('homepage/category.html.twig', [
          'breadcrumb' => $breadcrumb,
          'version' => $version,
      ]);
    }

    #[Route('/qui-sommes-nous', name: 'app_qui_sommes_nous',options: ['expose' => true])]
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

    #[Route('/suivi-de-mon-dossier', name: 'app_suivi_mon_dossier',options: ['expose' => true])]
    public function suiviDossier(Breadcrumb $breadcrumb, Version $version, Request $request): Response
    {
        /** @var string $raccourci */
        $raccourci = $request->get('raccourci')??"";
        $breadcrumb->add("homepage.title", null);
        $user = $this->getUser();
        if($user && $user->hasRole(User::ROLE_REQUERANT))
          $breadcrumb->add('requerant.homepage.title', 'app_requerant_homepage');

        $submittedToken = $request->getPayload()->get('_csrf_token');
        $statuts = [];
        if ($this->isCsrfTokenValid('authenticate', $submittedToken)) {
          $prejudice = $this
            ->em
            ->getRepository(Prejudice::class)
            ->findOneBy(['raccourci' => $raccourci])
          ;
          if(null !== $prejudice) {
            $tmp = $prejudice->getStatuts();
            foreach($tmp as $statut) {
              $statuts[]=[$statut->getDate()->format("d/m/Y H:i"), $statut->getLibelle()];
            }
          }
        }

        return $this->render('homepage/suivi_dossier.html.twig', [
            'breadcrumb' => $breadcrumb,
            'version' => $version,
            'raccourci' => $raccourci,
            'statuts' => $statuts,
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
