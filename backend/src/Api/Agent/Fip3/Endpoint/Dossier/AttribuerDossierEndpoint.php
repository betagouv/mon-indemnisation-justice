<?php

namespace MonIndemnisationJustice\Api\Agent\Fip3\Endpoint\Dossier;

use MonIndemnisationJustice\Api\Agent\Fip3\Voter\DossierVoter;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Repository\AgentRepository;
use MonIndemnisationJustice\Repository\BrisPorteRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/api/agent/fip3/dossier/{id}/attribuer', name: 'api_agent_fip3_dossier_attribuer', methods: ['POST'])]
#[IsGranted(DossierVoter::ACTION_ATTRIBUER, 'Seul un agent attributeur peut attribuer un dossier', 401)]
class AttribuerDossierEndpoint
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
        #[MapRequestPayload]
        AttribuerDossierInput $input,
        Security $security
    ) {
        if (!$dossier->estAAttribuer()) {
            return new JsonResponse(['erreur' => "Ce dossier n'est pas à attribuer"], Response::HTTP_BAD_REQUEST);
        }
        $agent = $this->agentRepository->find($input->redacteur_id);

        $dossier
            ->setRedacteur($agent)
            ->changerStatut(EtatDossierType::DOSSIER_A_INSTRUIRE, agent: $security->getUser(), contexte: [
                'redacteur' => $agent->getId(),
            ])
        ;
        $this->dossierRepository->save($dossier);

        return new JsonResponse([
            'etat' => $this->normalizer->normalize(
                $this->objectMapper->map($dossier->getEtatDossier(), AttribuerDossierOutput::class)
            ),
        ]);
    }
}
