<?php

namespace App\Controller\Prejudice;

use App\Entity\BrisPorte;
use App\Entity\Statut;
use App\Entity\User;
use App\Repository\StatutRepository;
use App\Service\Breadcrumb\Breadcrumb;
use App\Service\Mailer\BasicMailer;
use App\Service\Version\Version;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

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
        private StatutRepository $statutRepository,
        private TranslatorInterface $translator,
        protected readonly string $emailFrom,
        protected readonly string $emailFromLabel,
        protected readonly string $baseUrl,
    ) {
    }

    #[Route('/ajouter-un-bris-de-porte', name: 'app_bris_porte_add', methods: ['POST', 'GET'], options: ['expose' => true])]
    public function add(EntityManagerInterface $em, Request $request): Response
    {
        $brisPorte = $em->getRepository(BrisPorte::class)->newInstance($this->getUser());
        $session = $request->getSession();
        /** @var array $testEligibilite */
        $testEligibilite = $session->get('test_eligibilite', []);
        /** @var ?string $type */
        $type = $testEligibilite['type'] ?? null;
        /** @var ?\DateTime $dateOperationPJ */
        $dateOperationPJ = !empty($testEligibilite['dateOperationPJ']) ? new \DateTime($testEligibilite['dateOperationPJ']) : null;
        /** @var ?string $numeroPV */
        $numeroPV = $testEligibilite['numeroPV'] ?? null;
        /** @var ?string $numeroParquet */
        $numeroParquet = $testEligibilite['numeroParquet'] ?? null;
        /** @var bool $isErreurPorte */
        $isErreurPorte = $testEligibilite['isErreurPorte'] ? ('true' == $testEligibilite['isErreurPorte']) : false;
        if ('BRI' === $type) {
            $brisPorte->setDateOperationPJ($dateOperationPJ);
            $brisPorte->setNumeroPV($numeroPV);
            $brisPorte->setNumeroParquet($numeroParquet);
            $brisPorte->setIsErreurPorte($isErreurPorte);
            $serviceEnqueteur = $brisPorte->getServiceEnqueteur();
            $serviceEnqueteur->setNumeroPV($numeroPV);
            $serviceEnqueteur->setNumeroParquet($numeroParquet);
            $em->flush();
            $session->remove('test_eligibilite');
        }

        return $this->redirectToRoute('app_bris_porte_edit', ['id' => $brisPorte->getId()]);
    }

    #[IsGranted('prejudice_valid_or_reject', subject: 'brisPorte')]
    #[Route('/consulter-un-bris-de-porte/{id}', name: 'app_bris_porte_view', methods: ['GET'], options: ['expose' => true])]
    public function view(BrisPorte $brisPorte): Response
    {
        $breadcrumb = $this->breadcrumb;
        // $breadcrumb->add('homepage.title','app_homepage');
        $breadcrumb->add('requerant.homepage.title', 'app_redacteur_homepage');
        $breadcrumb->add(
            str_replace('%reference%', $brisPorte->getReference(), $this->translator->trans('bris_porte.accept_or_reject.title'))
        );

        return $this->render('prejudice/consulter_bris_porte.html.twig', [
            'breadcrumb' => $breadcrumb,
            'brisPorte' => $brisPorte,
            'version' => $this->version,
            'prejudice' => $brisPorte,
        ]);
    }

    #[IsGranted('edit', subject: 'brisPorte')]
    #[Route('/declarer-un-bris-de-porte/{id}', name: 'app_bris_porte_edit', methods: ['GET'], options: ['expose' => true])]
    public function edit(BrisPorte $brisPorte): Response
    {
        $breadcrumb = $this->breadcrumb;
        $breadcrumb->add('homepage.title', 'app_homepage');
        $breadcrumb->add('requerant.homepage.title', 'app_requerant_homepage');
        $breadcrumb->add('bris_porte.edit.title', null);

        return $this->render('prejudice/declare_bris_porte.html.twig', [
            'breadcrumb' => $breadcrumb,
            'brisPorte' => $brisPorte,
        ]);
    }

    #[Route('/passage-a-l-etat-constitue/{id}', name: 'app_requerant_update_statut_to_constitue', methods: ['GET'], options: ['expose' => true])]
    public function redirection(BrisPorte $brisPorte, BasicMailer $mailer): RedirectResponse
    {
        $user = $this->getUser();
        $this->statutRepository->addEvent($brisPorte, $user, Statut::CODE_CONSTITUE);

        $mailer
           ->from($this->emailFrom, $this->emailFromLabel)
           ->to($user->getEmail())
           ->subject($this->translator->trans('bris_porte.edit.email.title'))
           ->htmlTemplate('requerant/email/confirmation_passage_etat_constitue.html.twig', [
               'mail' => $user->getEmail(),
               'url' => $this->baseUrl,
               'nomComplet' => $user->getNomComplet(),
               'reference' => $brisPorte->getReference(),
               'raccourci' => $brisPorte->getRaccourci(),
           ])
           ->send(user: $user)
        ;

        return $this->redirectToRoute('app_requerant_homepage');
    }
}
