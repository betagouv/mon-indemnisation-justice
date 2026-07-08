<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Controller\Agent;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Service\DocumentManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\WebProfilerBundle\EventListener\WebDebugToolbarListener;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/agent/beta/previsualiser', condition: "env('APP_DEBUG')")]
class PrevisualiserController extends AbstractController
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly DocumentManager $documentManager,
        #[Autowire(service: 'web_profiler.debug_toolbar')]
        protected readonly WebDebugToolbarListener $toolbar,
    ) {
    }

    #[IsGranted(Agent::ROLE_AGENT_DOSSIER)]
    #[Route('/proposition-indemnisation/{dossierId}', name: 'agent_beta_previsualiser_proposition_indemnisation', methods: ['GET'])]
    public function previsualiserCourrierDossier(
        Request $request,
        int $dossierId = 0,
    ): Response {
        $this->toolbar->setMode(WebDebugToolbarListener::DISABLED);

        $dossier = $dossierId > 0 ? $this->em->getRepository(Dossier::class)->find($dossierId) : $this->em->getRepository(Dossier::class)->getDossierParEtat(EtatDossierType::DOSSIER_OK_A_SIGNER);

        return $this->render(
            'courrier/decision.html.twig',
            [
                'dossier' => $dossier,
                'corps' => $this->documentManager->genererCorps(
                    $dossier,
                    DocumentType::TYPE_COURRIER_MINISTERE,
                    montantIndemnisation: $request->query->has('montant') ?
                        floatval($request->query->get('montant')) :
                        $dossier->getMontantIndemnisation() ?? 1234.56
                ),
            ]
        );
    }
}
