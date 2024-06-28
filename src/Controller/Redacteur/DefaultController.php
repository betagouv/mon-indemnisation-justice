<?php

namespace App\Controller\Redacteur;

use App\Entity\Prejudice;
use App\Entity\User;
use App\Entity\Statut;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Service\Breadcrumb\Breadcrumb;
use App\Service\Version\Version;
use Doctrine\ORM\EntityManagerInterface;

#[IsGranted(User::ROLE_REDACTEUR_PRECONTENTIEUX)]
#[Route('/redacteur')]
class DefaultController extends AbstractController
{
    public function __construct(
      private Breadcrumb $breadcrumb,
      private Version $version,
      private EntityManagerInterface $em
    )
    {

    }

    #[Route('/accueil', name: 'app_redacteur_homepage', options: ['expose' => true])]
    public function index(): Response
    {
        $user       = $this->getUser();
        if($user->hasRole(User::ROLE_CHEF_PRECONTENTIEUX))
          return $this->redirectToRoute('app_chef_precontentieux_homepage');

        $em         = $this->em;
        $breadcrumb = $this->breadcrumb;
        //$breadcrumb->add('homepage.title','app_homepage');
        //$breadcrumb->add('requerant.homepage.title',null);
        $statuts = $em->getRepository(Statut::class)->findBy(['code' => [
          Statut::CODE_CONSTITUE
          ]]);
        $prejudices = $em
          ->getRepository(Prejudice::class)
          ->findByStatuts($statuts,[],0,10)
        ;
        return $this->render('redacteur/default/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'prejudices' => $prejudices,
            'version' => $this->version,
        ]);
    }
}
