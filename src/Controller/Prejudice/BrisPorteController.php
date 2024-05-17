<?php

namespace App\Controller\Prejudice;

use App\Entity\BrisPorte;
use App\Entity\Categorie;
use App\Entity\Statut;
use App\Entity\User;
use App\Repository\StatutRepository;
use App\Service\Version\Version;
use App\Service\Breadcrumb\Breadcrumb;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\ExpressionLanguage\Expression;

#[IsGranted(
  new Expression(
      'is_granted("'.User::ROLE_REQUERANT.'") or is_granted("'.User::ROLE_REDACTEUR_PRECONTENTIEUX.'")'
  )
)]
#[Route('/bris-de-porte')]
class BrisPorteController extends AbstractController
{
    public function __construct(
      private Version $version,
      private Breadcrumb $breadcrumb,
      private StatutRepository $statutRepository
    )
    {

    }

    #[Route('/ajouter-un-bris-de-porte', name: 'app_bris_porte_add', methods: ['POST', 'GET'], options: ['expose' => true])]
    public function add(EntityManagerInterface $em): Response
    {
      $brisPorte = $em->getRepository(BrisPorte::class)->newInstance($this->getUser());
      return $this->redirectToRoute('app_bris_porte_edit',['id' => $brisPorte->getId()]);
    }

    #[IsGranted('view', subject: 'brisPorte')]
    #[Route('/consulter-un-bris-de-porte/{id}', name: 'app_bris_porte_view', methods: ['GET'], options: ['expose' => true])]
    public function view(BrisPorte $brisPorte): Response
    {
      $breadcrumb = $this->breadcrumb;
      $breadcrumb->add('homepage.title','app_homepage');

      return $this->render('prejudice/consulter_bris_porte.html.twig', [
          'breadcrumb' => $breadcrumb,
          'brisPorte' => $brisPorte
      ]);
    }

    #[IsGranted('edit', subject: 'brisPorte')]
    #[Route('/declarer-un-bris-de-porte/{id}', name: 'app_bris_porte_edit', methods: ['GET'], options: ['expose' => true])]
    public function edit(BrisPorte $brisPorte): Response
    {
      $breadcrumb = $this->breadcrumb;
      $breadcrumb->add('homepage.title','app_homepage');
      $breadcrumb->add('requerant.homepage.title','app_requerant_homepage');
      $breadcrumb->add('bris_porte.edit.title', null);

      return $this->render('prejudice/declare_bris_porte.html.twig', [
          'breadcrumb' => $breadcrumb,
          'brisPorte' => $brisPorte
      ]);
    }

    #[Route('/passage-a-l-etat-constitue/{id}', name: 'app_requerant_update_statut_to_constitue', methods: ['GET'], options: ['expose' => true])]
    public function redirection(BrisPorte $brisPorte): RedirectResponse
    {
      $statut = new Statut();
      $statut->setCode(Statut::CODE_CONSTITUE);
      $statut->setPrejudice($brisPorte);
      $statut->setEmetteur($this->getUser());
      $this->statutRepository->add($statut,true);
      return $this->redirectToRoute('app_requerant_homepage');
    }
}
