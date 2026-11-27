<?php

namespace MonIndemnisationJustice\Api\Requerant\Dossier;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Requerant\Dossier\Dto\ContexteSignatureElectroniqueDto;
use MonIndemnisationJustice\Api\Requerant\Dossier\Dto\DossierDto;
use MonIndemnisationJustice\Api\Requerant\Voter\RequerantDossierVoter;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\EtablissementBancaire;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Entity\ReferenceBancaire;
use MonIndemnisationJustice\Entity\RequeteSignatureRequerant;
use MonIndemnisationJustice\Message\ActivationSignatureRequerant;
use MonIndemnisationJustice\Message\GenerationReleveIdentiteBancaire;
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
        protected readonly EntityManagerInterface $em,
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

        // Une nouvelle signature ne peut remplacer l'actuelle qu'une fois celle-ci fermée (expirée ou annulée)
        if (!$dossier->peutDemarrerSignature()) {
            return new JsonResponse([
                'erreur' => 'Une signature est déjà en cours pour ce dossier',
            ], Response::HTTP_BAD_REQUEST);
        }

        $referenceBancaire = $this->enregistrerReferenceBancaire($dossier, $contexte);

        $requeteSignature = $this->gestionnaireSignature->initierSignatureRequerant($dossier);
        $requeteSignature->setReferenceBancaire($referenceBancaire);
        $this->em->flush();

        // La déclaration d'acceptation et le relevé d'identité bancaire sont générés par le worker
        $bus->dispatch(new ActivationSignatureRequerant(id: $requeteSignature->getId()));
        $bus->dispatch(new GenerationReleveIdentiteBancaire(idReferenceBancaire: $referenceBancaire->getId(), idDossier: $dossier->getId()));

        return new JsonResponse(
            $this->normalizer->normalize(DossierDto::depuisDossier($requeteSignature->getDossier()), 'json'),
            Response::HTTP_OK
        );
    }

    /**
     * Met à jour la référence bancaire actuelle du dossier tant qu'aucune requête de signature n'y est rattachée ; sinon
     * en crée une nouvelle, pour que chaque requête passée conserve les coordonnées avec lesquelles elle a été émise.
     */
    protected function enregistrerReferenceBancaire(Dossier $dossier, ContexteSignatureElectroniqueDto $contexte): ReferenceBancaire
    {
        $actuelle = $dossier->getReferenceBancaire();
        $estDejaUtilisee = null !== $actuelle && $dossier->getRequetesSignatureRequerant()->exists(
            fn (int $index, RequeteSignatureRequerant $requete) => $requete->getReferenceBancaire() === $actuelle
        );

        $referenceBancaire = $contexte->appliquer($estDejaUtilisee ? null : $actuelle);

        $codeBanque = $referenceBancaire->getCodeBanque();
        $referenceBancaire->setEtablissement(null !== $codeBanque ? $this->em->find(EtablissementBancaire::class, $codeBanque) : null);

        $dossier->setReferenceBancaire($referenceBancaire);
        $this->em->persist($referenceBancaire);

        return $referenceBancaire;
    }
}
