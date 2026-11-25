<?php

namespace MonIndemnisationJustice\Api\Requerant\Dossier;

use MonIndemnisationJustice\Api\Requerant\Dossier\Dto\ContexteSignatureElectroniqueDto;
use MonIndemnisationJustice\Api\Requerant\Dossier\Dto\DossierDto;
use MonIndemnisationJustice\Api\Requerant\Voter\RequerantDossierVoter;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Message\ActivationSignatureRequerant;
use MonIndemnisationJustice\Service\Signature\GestionnaireSignature;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[Route('/api/requerant/dossier/bris-de-porte/{id}/demarrer-signature', name: 'api_requerant_dossier_bris_porte_demarrer_signature', methods: ['POST'])]
#[IsGranted(RequerantDossierVoter::ACTION_DOSSIER_DEMARRER_SIGNATURE, 'dossier', message: "Seul le requérant peut démarrer la signature afin d'accepter l'indemnisation de son dossier", statusCode: Response::HTTP_FORBIDDEN)]
class DemarrerSignatureEndpoint
{
    public function __construct(
        protected readonly GestionnaireSignature $gestionnaireSignature,
        protected readonly NormalizerInterface $normalizer,
    ) {
    }

    public function __invoke(
        #[MapEntity]
        Dossier $dossier,
        #[MapRequestPayload]
        ContexteSignatureElectroniqueDto $contexte,
        MessageBusInterface $bus,
    ) {
        if (EtatDossierType::DOSSIER_OK_A_APPROUVER !== $dossier->getEtatDossier()->getEtat()) {
            return new JsonResponse([
                'erreur' => "Ce dossier n'est pas en attente d'approbation",
            ], Response::HTTP_BAD_REQUEST);
        }

        // TODO vérifier que le dossier n'a pas déjà une signature active

        $requeteSignature = $this->gestionnaireSignature->initierSignatureRequerant($dossier);
        $bus->dispatch(new ActivationSignatureRequerant(id: $requeteSignature->getId(), contexte: $contexte));


        return new JsonResponse(
            $this->normalizer->normalize(DossierDto::depuisDossier($requeteSignature->getDossier()), 'json'),
            Response::HTTP_OK
        );
    }
}
