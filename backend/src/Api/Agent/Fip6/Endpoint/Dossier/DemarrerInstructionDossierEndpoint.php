<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier;

use MonIndemnisationJustice\Api\Agent\Fip6\Output\EtatDossierOutput;
use MonIndemnisationJustice\Api\Agent\Fip6\Voter\DossierVoter;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Repository\AgentRepository;
use MonIndemnisationJustice\Repository\BrisPorteRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/api/agent/fip6/dossier/{id}/demarrer-instruction', name: 'api_agent_fip6_dossier_demarrer_instruction', methods: ['POST'])]
#[IsGranted(DossierVoter::ACTION_INSTRUIRE, 'dossier', message: "Seul l'agent rédacteur attribué peut instruire un dossier", statusCode: Response::HTTP_FORBIDDEN)]
class DemarrerInstructionDossierEndpoint
{
    public function __construct(
        protected readonly NormalizerInterface $normalizer,
        protected readonly ObjectMapperInterface $objectMapper,
        protected readonly AgentRepository $agentRepository,
        protected readonly BrisPorteRepository $dossierRepository,
    ) {}

    public function __invoke(
        #[MapEntity]
        BrisPorte $dossier,
        Security $security
    ) {
        if (EtatDossierType::DOSSIER_A_INSTRUIRE === !$dossier->getEtatDossier()->getEtat()) {
            return new JsonResponse(['erreur' => "Ce dossier n'est pas en attente d'instruction"], Response::HTTP_BAD_REQUEST);
        }

        $dossier
            ->changerStatut(EtatDossierType::DOSSIER_EN_INSTRUCTION, agent: $security->getUser())
        ;

        $this->dossierRepository->save($dossier);

        return new JsonResponse([
            'etat' => $this->normalizer->normalize(
                $this->objectMapper->map($dossier->getEtatDossier(), EtatDossierOutput::class)
            ),
        ]);
    }
}
