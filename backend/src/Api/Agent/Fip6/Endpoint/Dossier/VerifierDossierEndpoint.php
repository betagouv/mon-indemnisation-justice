<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Agent\Fip6\Voter\DossierVoter;
use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\DossierValidation;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Entity\PieceJointeValidation;
use MonIndemnisationJustice\Service\DossierManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/agent/fip6/dossier/{id}/verifier', name: 'api_agent_fip6_dossier_vetifier', methods: ['POST'])]
#[IsGranted(DossierVoter::ACTION_VERIFIER, subject: 'dossier', message: 'Seul le rédacteur peut vérifier le dossier qui lui est attribué', statusCode: Response::HTTP_FORBIDDEN)]
class VerifierDossierEndpoint
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly DossierManager $dossierManager,
    ) {
    }

    public function __invoke(
        #[MapEntity]
        Dossier $dossier,
        #[MapRequestPayload] VerifierDossierInput $entree,
        Security $security,
    ) {
        if (EtatDossierType::DOSSIER_A_VERIFIER !== $dossier->getEtatDossier()->getEtat()) {
            throw new BadRequestHttpException("Ce dossier n'est pas à vérifier");
        }

        $validation = new DossierValidation()
            ->setRecevable($entree->estRecevable)
            ->setCommentaire($entree->commentaire)
            ->setPiecesJointes(
                $dossier->getPiecesJointes()
                    ->map(
                        fn (Document $pieceJointe) => new PieceJointeValidation()
                            ->setPieceJointe($pieceJointe)
                            ->setRecevable($entree->piecesJointes[$pieceJointe->getId()]?->estRecevable ?? false)
                            ->setCommentaire($entree->piecesJointes[$pieceJointe->getId()]?->commentaire)
                    )->toArray()
            );

        $dossier->valider($validation);
        $this->em->persist($dossier);
        $this->em->flush();

        if ($entree->estRecevable) {
            $this->dossierManager->avancer($dossier, agent: $security->getUser(), contexte: [
                'validation' => $validation->getId()->__toString(),
            ]);
        } else {
            $this->dossierManager->changer($dossier, EtatDossierType::DOSSIER_A_COMPLETER, agent: $security->getUser(), contexte: [
                'validation' => $validation->getId()->__toString(),
            ]);
        }

        return new JsonResponse([]);
    }
}
