<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Endpoint\BrisDePorte;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Agent\FDO\Input\DeclarationFDOBrisPorteInput;
use MonIndemnisationJustice\Api\Agent\FDO\Input\DocumentDto;
use MonIndemnisationJustice\Api\Agent\FDO\Voter\DeclarationFDOBrisPorteVoter;
use MonIndemnisationJustice\Entity\BrouillonDeclarationFDOBrisPorte;
use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Service\DocumentManager;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapUploadedFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\ObjectMapper\ObjectMapperInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Exception\ExtraAttributesException;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Route API qui permet à un agent des FDO de déclarer une erreur opérationnelle.
 */
#[Route('/api/agent/fdo/bris-de-porte/{id}/televerser-piece-jointe/{type}', name: 'api_agent_fdo_bris_porte_televerser_piece_jointe', methods: ['POST'])]
#[IsGranted(
    DeclarationFDOBrisPorteVoter::ACTION_DECLARER,
    message: "La déclaration d'une erreur opérationnelle est retreinte aux agents des Forces de l'Ordre",
    statusCode: Response::HTTP_FORBIDDEN
)]
class TeleverserPieceJointeDeclarationBrisPorteEndpoint
{
    public function __construct(
        protected readonly EntityManagerInterface $em,
        protected readonly ObjectMapperInterface $objectMapper,
        protected readonly NormalizerInterface $normalizer,
        protected readonly DenormalizerInterface $denormalizer,
        protected readonly ValidatorInterface $validator,
        protected readonly DocumentManager $documentManager,
    ) {}

    public function __invoke(
        Uuid $id,
        Request $request,
        Security $security,
        string $type,
        #[MapUploadedFile(name: 'pieceJointe')]
        UploadedFile $fichierTeleverse
    ): Response {
        /** @var BrouillonDeclarationFDOBrisPorte $brouillon */
        $brouillon = $this->em->find(BrouillonDeclarationFDOBrisPorte::class, $id);

        if (null === ($documentType = DocumentType::tryFrom($type)) || !in_array($documentType, [DocumentType::TYPE_PV_FDO, DocumentType::TYPE_PHOTO_FDO])) {
            throw new BadRequestHttpException('Type de pièce jointe non reconnu');
        }

        // 1. Créer le document
        $pieceJointe = (new Document())
            ->setType($documentType)
            ->setOriginalFilename($fichierTeleverse->getClientOriginalName())
            ->setAjoutRequerant(true)
            ->setMime($fichierTeleverse->getClientMimeType())
            ->setAjoutRequerant(false)
        ;

        $this->documentManager->enregistrerDocument($pieceJointe, $fichierTeleverse->getContent());

        $this->em->persist($pieceJointe);
        $this->em->flush();

        // 2. Rattacher le document au brouillon
        $brouillon->ajouterPieceJointe(
            $this->normalizer->normalize(
                $this->objectMapper->map($pieceJointe, DocumentDto::class),
                'json'
            )
        );

        try {
            $input = $this->denormalizer->denormalize($brouillon->getDonnees(), DeclarationFDOBrisPorteInput::class, context: [AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => false]);
        } catch (UnexpectedValueException $e) {
            $this->em->remove($pieceJointe);
            $this->em->flush();

            return new JsonResponse([
                'erreur' => $e->getMessage(),
            ], Response::HTTP_BAD_REQUEST);
        } catch (ExtraAttributesException $e) {
            $this->em->remove($pieceJointe);
            $this->em->flush();

            return new JsonResponse([
                'erreur' => (count($e->getExtraAttributes()) > 1 ? 'Champs non reconnus' : 'Champ non reconnu').': '.implode(', ', $e->getExtraAttributes()),
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->em->persist($brouillon);
        $this->em->flush();

        return new JsonResponse(
            $this->normalizer->normalize(
                $input,
                'json',
                // [AbstractObjectNormalizer::SKIP_NULL_VALUES => true]
            ),
            Response::HTTP_CREATED
        );
    }
}
