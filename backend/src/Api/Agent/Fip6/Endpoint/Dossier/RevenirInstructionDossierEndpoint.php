<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier;

use MonIndemnisationJustice\Api\Agent\Fip6\Output\DossierDetailOutput;
use MonIndemnisationJustice\Api\Agent\Fip6\Voter\DossierVoter;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Repository\DossierRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/api/agent/fip6/dossier/{id}/revenir-instruction', name: 'api_agent_fip6_dossier_revenir_instruction', methods: ['POST'])]
#[IsGranted(DossierVoter::ACTION_REVENIR_INSTRUCTION, subject: 'dossier', message: 'Seul le rédacteur attribué peut faire revenir ce dossier en instruction', statusCode: Response::HTTP_FORBIDDEN)]
class RevenirInstructionDossierEndpoint
{
    public function __construct(
        protected readonly DossierRepository $dossierRepository,
    ) {
    }

    #[Serialize]
    public function __invoke(
        #[MapEntity]
        Dossier $dossier,
        NormalizerInterface $normalizer,
        Security $security,
    ) {
        if (!in_array(
            $dossier->getEtatDossier()->getEtat(),
            [
                EtatDossierType::DOSSIER_OK_A_SIGNER,
                EtatDossierType::DOSSIER_KO_A_SIGNER,
                EtatDossierType::DOSSIER_OK_A_APPROUVER,
            ],
            true
        )) {
            return new JsonResponse(['erreur' => "Ce dossier n'est pas éligible pour revenir à l'instruction"], Response::HTTP_BAD_REQUEST);
        }

        $dossier->changerStatut(EtatDossierType::DOSSIER_EN_INSTRUCTION, agent: $security->getUser(), contexte: [
            'retour' => true,
        ]);

        $this->dossierRepository->save($dossier);

        return DossierDetailOutput::creerDepuisDossier($dossier);
    }
}
