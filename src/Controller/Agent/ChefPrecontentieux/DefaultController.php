<?php

namespace App\Controller\Agent\ChefPrecontentieux;

use App\Entity\Prejudice;
use App\Entity\Statut;
use App\Entity\User;
use App\Service\Breadcrumb\Breadcrumb;
use App\Service\Version\Version;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(User::ROLE_CHEF_PRECONTENTIEUX)]
#[Route('/chef-precontentieux')]
class DefaultController extends AbstractController
{
    public function __construct(
      private Breadcrumb $breadcrumb,
      private Version $version,
      private EntityManagerInterface $em
    )
    {

    }

    #[Route('/accueil', name: 'app_chef_precontentieux_homepage', options: ['expose' => true])]
    public function index(): Response
    {
        $em         = $this->em;
        $breadcrumb = $this->breadcrumb;
        //$breadcrumb->add('homepage.title','app_homepage');
        //$breadcrumb->add('requerant.homepage.title',null);
        $statuts = $em->getRepository(Statut::class)->findBy(['code' => [
          Statut::CODE_VALIDE, Statut::CODE_REJETE
        ]]);
        $prejudices = $em
          ->getRepository(Prejudice::class)
          ->findByStatuts($statuts,[],0,10)
        ;
        return $this->render('chef_precontentieux/default/index.html.twig', [
            'breadcrumb' => $breadcrumb,
            'prejudices' => $prejudices,
            'version' => $this->version,
        ]);
    }
}
