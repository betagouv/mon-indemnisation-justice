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

class PrejudiceController extends AbstractController
{
    public function __construct(
      private Version $version,
      private Breadcrumb $breadcrumb,
      private StatutRepository $statutRepository
    )
    {

    }

    #[Route('/bris-de-porte/tester-mon-eligibilite', name: 'app_bris_porte_test_eligibilite', methods: ['POST', 'GET'], options: ['expose' => true])]
    public function testEligibilite(): Response
    {
      $breadcrumb = $this->breadcrumb;
      $breadcrumb->add('homepage.title','app_homepage');
      $breadcrumb->add('bris_porte.test_eligibilite.title',null);

      return $this->render('prejudice/bris_porte/test_eligibilite.html.twig', [
          'breadcrumb' => $breadcrumb,
          'version' => $this->version,
      ]);
    }
}
