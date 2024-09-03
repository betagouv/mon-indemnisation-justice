<?php
namespace App\Controller\Agent;

use App\Entity\Agent;
use App\Entity\BrisPorte;
use App\Entity\Statut;
use Clegginabox\PDFMerger\PDFMerger;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[IsGranted(Agent::ROLE_AGENT_REDACTEUR)]
#[Route('/agent/decision')]
class DecisionController extends AbstractController
{
    public function __construct(
      private EntityManagerInterface $em,
      private Pdf $pdf
    ) {
    }

    private function printRefusBRI(BrisPorte $brisPorte): Response
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
      ;
      return new Response(
        $this->pdf->getOutputFromHtml($template->getContent(),[
          'page-size' => 'A4',
          'footer-html' => $footer->getContent(),
          'header-html' => $header->getContent(),
          'margin-top' => '10mm',
          'margin-bottom' => '20mm',
          'footer-center' => '',
          'encoding' => 'utf-8',
          'enable-local-file-access' => true,
        ]),
        200,
        [
          'Content-Type' => 'application/pdf',
          'Content-Disposition' => 'inline; filename="'.$brisPorte->getId().'.pdf"',
        ]
      );
    }

    #[IsGranted('edit', subject: 'brisPorte')]
    #[Route('/bris-de-porte/previsionnel/{type}/{id}.pdf', name: 'app_decision_bri_previsionnel_print',options: ['expose' => true])]
    public function printPrevisionnelDecisionBRI(BrisPorte $brisPorte, Request $request): Response
    {
      $type = $request->get('type');
      switch($type) {
        case Statut::CODE_VALIDE:
          return $this->printPropositionCompileeBRI($brisPorte);
        case Statut::CODE_REJETE:
          return $this->printRefusBRI($brisPorte);
        default:
      }
      throw new NotFoundHttpException('Document non trouvé');
    }
    #[IsGranted('view', subject: 'brisPorte')]
    #[Route('/bris-de-porte/{id}.pdf', name: 'app_decision_bri_print',options: ['expose' => true])]
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
        $this->pdf->getOutputFromHtml($template->getContent(),[
          'page-size' => 'A4',
          'footer-html' => $footer->getContent(),
          'header-html' => $header->getContent(),
          'margin-top' => '10mm',
          'margin-bottom' => '20mm',
          'footer-center' => '',
          'encoding' => 'utf-8',
          'enable-local-file-access'=> true
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
