<?php
namespace App\Controller\Decision;

use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\BrisPorte;
use App\Contracts\PrejudiceInterface;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DefaultController extends AbstractController
{
    #[IsGranted('view', subject: 'brisPorte')]
    #[Route('/decision-sur-bris-de-porte/rejet/{id}.pdf', name: 'app_decision_bri_rejet_print')]
    public function printBRI(BrisPorte $brisPorte, Pdf $pdf): Response
    {
      $user = $this->getUser();
      $footer = $this->render('decision/footer.html.twig', [
        'user'=> $user,
        'base_dir'  => $this->getParameter('kernel.project_dir').'/public',
      ]);

      $header = $this->render('decision/header.html.twig', [
        'user'=> $user,
        'base_dir'  => $this->getParameter('kernel.project_dir').'/public',
      ]);

      $template = $this->render('decision/rejet/BRI/content.html.twig',[
        'prejudice'   => $brisPorte,
        'user' => $user,
        'base_dir'  => $this->getParameter('kernel.project_dir').'/public',
      ]);
      $pdf->setOption('footer-center', 'Page [page]');
      return new Response(
        $pdf->getOutputFromHtml($template->getContent(),[
          'page-size' => 'A4',
          'footer-html' => $footer->getContent(),
          'header-html' => $header->getContent(),
          'margin-top' => '10mm',
          'margin-bottom' => '20mm',
          'footer-center' => '',
          'encoding' => 'utf-8',
        ]),
        200,
        [
          'Content-Type' => 'application/pdf',
          'Content-Disposition' => 'inline; filename="'.$brisPorte->getId().'.pdf"',
        ]
      );

    }
}
