<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Endpoint\BrisDePorte;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Agent\FDO\Dto\DeclarationFDOBrisPorteDto;
use MonIndemnisationJustice\Api\Agent\FDO\Voter\DeclarationFDOBrisPorteVoter;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Service\DocumentManager;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\Serialize;
use Symfony\Component\HttpKernel\Attribute\MapUploadedFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Route API qui permet à un agent des FDO de téléverser une pièce jointe au brouillon d'une déclaration d'erreur
 * opérationnelle.
 */
#[Route('/api/agent/fdo/bris-de-porte/{declarationId}/piece-jointe/televerser/{type}', name: 'api_agent_fdo_bris_porte_televerser_piece_jointe', methods: ['POST'])]
#[IsGranted(
    DeclarationFDOBrisPorteVoter::ACTION_AJOUTER_PJ,
    'declaration',
    message: "La déclaration d'une erreur opérationnelle est retreinte aux agents des Forces de l'Ordre",
    statusCode: Response::HTTP_FORBIDDEN
)]
#[Serialize(code: Response::HTTP_CREATED)]
class TeleverserPieceJointeDeclarationBrisPorteEndpoint
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly DocumentManager $documentManager,
    ) {
    }

    public function __invoke(
        #[MapEntity(id: 'declarationId', message: 'Déclaration inconnue')]
        DeclarationFDOBrisPorte $declaration,
        string $type,
        #[MapUploadedFile(name: 'pieceJointe')]
        UploadedFile $fichierTeleverse,
    ): DeclarationFDOBrisPorteDto {
        if (null === ($documentType = DocumentType::tryFrom($type)) || !in_array($documentType, [DocumentType::TYPE_PV_FDO, DocumentType::TYPE_PHOTO_FDO])) {
            throw new BadRequestHttpException('Type de pièce jointe non reconnu');
        }

        $mime = $this->documentManager->verifierFichierTeleverse($fichierTeleverse);

        // 1. Créer le document
        $pieceJointe = (new Document())
            ->setType($documentType)
            ->setOriginalFilename($fichierTeleverse->getClientOriginalName())
            ->setAjoutRequerant(false)
            ->setMime($mime)
        ;

        $pieceJointe = $this->documentManager->enregistrerDocument($pieceJointe, $fichierTeleverse->getContent(), DocumentManager::TYPES_AUTORISES[$mime]);

        // 2. Rattacher le document à la déclaration
        $declaration->ajouterPieceJointe($pieceJointe);

        $this->em->persist($pieceJointe);
        $this->em->persist($declaration);
        $this->em->flush();

        return DeclarationFDOBrisPorteDto::depuisDeclaration($declaration);
    }
}
