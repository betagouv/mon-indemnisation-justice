<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier;

use MonIndemnisationJustice\Api\Agent\Fip6\Output\DossierDetailOutput;
use MonIndemnisationJustice\Api\Agent\Fip6\Voter\DossierVoter;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Repository\AgentRepository;
use MonIndemnisationJustice\Repository\DossierRepository;
use MonIndemnisationJustice\Service\Mailer;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/api/agent/fip6/dossier/{id}/attribuer', name: 'api_agent_fip6_dossier_attribuer', methods: ['POST'])]
#[IsGranted(DossierVoter::ACTION_ATTRIBUER, message: 'Seul un agent attributeur peut attribuer un dossier attribuable', statusCode: Response::HTTP_FORBIDDEN)]
class AttribuerDossierEndpoint
{
    public function __construct(
        protected readonly NormalizerInterface $normalizer,
        protected readonly ObjectMapperInterface $objectMapper,
        protected readonly AgentRepository $agentRepository,
        protected readonly DossierRepository $dossierRepository,
        protected readonly Mailer $mailer,
    ) {
    }

    public function __invoke(
        #[MapEntity]
        Dossier $dossier,
        #[MapRequestPayload]
        AttribuerDossierInput $input,
        Security $security,
    ) {
        if (!($dossier->estAAttribuer() || $dossier->peutEtreReattribue())) {
            return new JsonResponse(['erreur' => "Ce dossier n'est pas à attribuer"], Response::HTTP_BAD_REQUEST);
        }
        $agent = $this->agentRepository->find($input->redacteur_id);

        if (!$agent->estRedacteur()) {
            return new JsonResponse(['erreur' => "Cet agent n'est pas rédacteur"], Response::HTTP_BAD_REQUEST);
        }

        $predecesseur = $dossier->getRedacteur();
        $estReattribution = null !== $predecesseur;

        if ($agent === $predecesseur) {
            return new JsonResponse(['erreur' => 'Ce dossier est déjà attribué à ce rédacteur'], Response::HTTP_BAD_REQUEST);
        }

        $dossier
            ->setRedacteur($agent);

        if (!$estReattribution) {
            $dossier->changerStatut(EtatDossierType::DOSSIER_A_INSTRUIRE, agent: $security->getUser(), contexte: [
                'redacteur' => $agent->getId(),
            ]);
        }

        $this->dossierRepository->save($dossier);

        if ($estReattribution) {
            // Informer le nouveau rédacteur de la ré-attribution, avec son prédécesseur en copie :
            $this->mailer
                ->toAgent($agent, [$predecesseur])
                ->subject('Mon Indemnisation Justice: un dossier vous a été ré-attribué')
                ->htmlTemplate('email/agent/fip6/dossier_reattribue.html.twig', [
                    'dossier' => $dossier,
                    'agent' => $agent,
                    'predecesseur' => $predecesseur,
                ])
                ->send();
        }

        return new JsonResponse(
            $this->normalizer->normalize(DossierDetailOutput::creerDepuisDossier($dossier), 'json')
        );
    }
}
