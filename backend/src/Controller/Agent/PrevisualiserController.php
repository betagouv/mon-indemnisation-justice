<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Controller\Agent;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Entity\MotifRejetBrisPorte;
use MonIndemnisationJustice\Service\DocumentManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\WebProfilerBundle\EventListener\WebDebugToolbarListener;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
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
        protected readonly ?WebDebugToolbarListener $toolbar = null,
    ) {
    }

    #[IsGranted(Agent::ROLE_AGENT_DOSSIER)]
    #[Route('/proposition-indemnisation/{dossierId}', name: 'agent_beta_previsualiser_proposition_indemnisation', methods: ['GET'])]
    public function propositionIndemnisation(
        Request $request,
        int $dossierId = 0,
    ): Response {
        $this->toolbar?->setMode(WebDebugToolbarListener::DISABLED);

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

    #[IsGranted(Agent::ROLE_AGENT_DOSSIER)]
    #[Route('/declaration-acceptation/', name: 'agent_beta_previsualiser_declaration_aceptation_aleatoire', methods: ['GET'])]
    public function declarationAcceptationAleatoire()
    {
        return $this->redirectToRoute(
            'agent_beta_previsualiser_declaration_aceptation',
            [
                'dossierId' => $this->em->getRepository(Dossier::class)->getDossierParEtatAleatoire(EtatDossierType::DOSSIER_OK_A_APPROUVER)->getId(),
            ]
        );
    }

    #[IsGranted(Agent::ROLE_AGENT_DOSSIER)]
    #[Route('/declaration-acceptation/{dossierId}', name: 'agent_beta_previsualiser_declaration_aceptation', methods: ['GET'])]
    public function declarationAcceptation(
        Request $request,
        #[MapEntity(Dossier::class, id: 'dossierId')]
        Dossier $dossier,
    ): Response {
        $this->toolbar?->setMode(WebDebugToolbarListener::DISABLED);

        return $this->render(
            'courrier/declarationAcceptation.html.twig',
            [
                'dossier' => $dossier,
                'corps' => $this->documentManager->genererCorps(
                    $dossier,
                    DocumentType::TYPE_COURRIER_REQUERANT,
                    montantIndemnisation: $request->query->has('montant') ?
                        floatval($request->query->get('montant')) :
                        $dossier->getMontantIndemnisation() ?? 1234.56
                ),
            ]
        );
    }

    #[IsGranted(Agent::ROLE_AGENT_DOSSIER)]
    #[Route('/rejet/{motif}', name: 'agent_beta_previsualiser_rejet_aleatoire', methods: ['GET'])]
    public function rejetAleatoire(
        string $motif,
    ) {
        return $this->redirectToRoute(
            'agent_beta_previsualiser_rejet',
            [
                'motif' => $motif,
                'dossierId' => $this->em->getRepository(Dossier::class)->getDossierParEtatAleatoire(EtatDossierType::DOSSIER_KO_A_SIGNER)->getId(),
            ]
        );
    }

    #[IsGranted(Agent::ROLE_AGENT_DOSSIER)]
    #[Route('/rejet/{dossierId}/{motif}', name: 'agent_beta_previsualiser_rejet', methods: ['GET'])]
    public function rejet(
        Request $request,
        #[MapEntity(Dossier::class, id: 'dossierId')]
        Dossier $dossier,
        string $motif,
    ): Response {
        $this->toolbar?->setMode(WebDebugToolbarListener::DISABLED);

        $motifRejet = MotifRejetBrisPorte::tryFrom(strtoupper($motif));

        if (null === $motifRejet) {
            throw new BadRequestException('Aucun motif trouvé pour '.strtoupper($motif));
        }

        return $this->render(
            'courrier/decision.html.twig',
            [
                'dossier' => $dossier,
                'corps' => $this->documentManager->genererCorps(
                    $dossier,
                    DocumentType::TYPE_COURRIER_MINISTERE,
                    motifRejet: $motifRejet
                ),
            ]
        );
    }
}
