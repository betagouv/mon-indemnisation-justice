<?php
namespace App\Controller\Decision;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use App\Entity\BrisPorte;
use App\Entity\Statut;
use App\Contracts\PrejudiceInterface;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Clegginabox\PDFMerger\PDFMerger;
use Doctrine\ORM\EntityManagerInterface;

class DefaultController extends AbstractController
{
    public function __construct(
      private EntityManagerInterface $em,
      private Pdf $pdf
    ) {
    }

    private function printRefusBRI(BrisPorte $brisPorte): Response
    {
      $pdf = $this->pdf;
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

    #[IsGranted('view', subject: 'brisPorte')]
    #[Route('/decision-sur-bris-de-porte/{id}.pdf', name: 'app_decision_bri_print')]
    public function printDecisionBRI(BrisPorte $brisPorte): Response
    {
      $lastStatut = $brisPorte->getLastStatut();
      switch($lastStatut->getCode()) {
        case Statut::CODE_VALIDE:
          return $this->printPropositionCompileeBRI($brisPorte);
        case Statut::CODE_REJETE:
          return $this->printRefusBRI($brisPorte);
        default:
      }
      throw new NotFoundHttpException('Document non trouvé');
    }

    private function printPropositionCompileeBRI(BrisPorte $brisPorte): Response
    {
      $pdf = $this->pdf;
      /** @var string $generatedPdf */
      $generatedPdf     = tempnam(sys_get_temp_dir(), 'TMP_');
      @unlink($generatedPdf);

      /** @var PDFMerger $pm */
      $pm = new PDFMerger();
      /** proposition d'acceptation */
      $firstDoc= $this->printPropositionBRI(brisPorte: $brisPorte);
      $generatedFirstDoc= tempnam(sys_get_temp_dir(), 'TMP_');
      file_put_contents($generatedFirstDoc, $firstDoc->getContent());
      $pm->addPDF($generatedFirstDoc,'all');
      /** \proposition d'acceptation */
      /** formulaire d'acceptation */
      $secondDoc= $this->printAcceptationDecisionBRI(brisPorte: $brisPorte);
      $generatedSecondDoc= tempnam(sys_get_temp_dir(), 'TMP_');
      file_put_contents($generatedSecondDoc, $secondDoc->getContent());
      $pm->addPDF($generatedSecondDoc,'all');
      /** \formulaire d'acceptation */
      $pm->merge('file', $generatedPdf);
      $content = file_get_contents($generatedPdf);
      @unlink($generatedFirstDoc);
      @unlink($generatedSecondDoc);
      return new Response(
        $content,
        200,
        [
          'Content-Type' => 'application/pdf',
          'Content-Disposition' => 'inline; filename="'.$brisPorte->getId().'.pdf"',
        ]
      );
    }

    private function printPropositionBRI(BrisPorte $brisPorte): Response
    {
      $pdf = $this->pdf;
      $user = $this->getUser();
      $footer = $this->render('decision/footer.html.twig', [
        'user'=> $user,
        'base_dir'  => $this->getParameter('kernel.project_dir').'/public',
      ]);

      $header = $this->render('decision/header.html.twig', [
        'user'=> $user,
        'base_dir'  => $this->getParameter('kernel.project_dir').'/public',
      ]);

      $template = $this->render('decision/proposition/BRI/content.html.twig',[
        'prejudice'   => $brisPorte,
        'user' => $user,
        'base_dir'  => $this->getParameter('kernel.project_dir').'/public',
      ]);
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

    private function printAcceptationDecisionBRI(BrisPorte $brisPorte): Response
    {
      $pdf = $this->pdf;
      $user = $this->getUser();

      $header = $this->render('decision/header.html.twig', [
        'user'=> $user,
        'base_dir'  => $this->getParameter('kernel.project_dir').'/public',
      ]);

      $template = $this->render('decision/acceptation/BRI/content.html.twig',[
        'prejudice'   => $brisPorte,
        'user' => $user,
        'base_dir'  => $this->getParameter('kernel.project_dir').'/public',
      ]);
      return new Response(
        $pdf->getOutputFromHtml($template->getContent(),[
          'page-size' => 'A4',
          'header-html' => $header->getContent(),
          'margin-top' => '10mm',
          'margin-bottom' => '20mm',
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
