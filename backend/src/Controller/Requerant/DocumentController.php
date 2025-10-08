<?php

namespace MonIndemnisationJustice\Controller\Requerant;

use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Entity\Requerant;
use MonIndemnisationJustice\Service\DocumentManager;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(Requerant::ROLE_REQUERANT)]
#[Route('/requerant/document')]
class DocumentController extends AbstractController
{
    public function __construct(
        protected readonly DocumentManager $documentManager,
        protected readonly LoggerInterface $logger,
    ) {}

    #[Route('/{id}/{type}', name: 'document_upload', methods: ['POST'])]
    public function upload(#[MapEntity(id: 'id')] BrisPorte $dossier, Request $request, DocumentType $type): JsonResponse
    {
        /** @var UploadedFile $file */
        $file = $request->files->get('piece-jointe');

        if (!$file->isValid()) {
            $this->logger->warning('Téléversement échoué', [
                'erreur' => $file->getError(),
                'original_name' => $file->getClientOriginalName(),
                'mime' => $file->getClientMimeType(),
            ]);

            throw new BadRequestException($file->getError());
        }

        if (null === $file?->getPathname()) {
            throw new BadRequestException('Impossible de lire le contenu de la pièce jointe');
        }

        $document = $this->documentManager->ajouterFichierTeleverse($dossier, $file, $type);

        return new JsonResponse([
            'id' => $document->getId(),
            'filename' => $document->getFilename(),
            'size' => $document->getSize(),
            'mime' => $document->getMime(),
            'type' => $document->getType(),
            'originalFilename' => $document->getOriginalFilename(),
        ]);
    }

    #[Route('/{id}/{filename}', name: 'document_remove', methods: ['DELETE'])]
    public function remove(#[MapEntity(id: 'id')] Document $document, string $filename): Response
    {
        if ($document->getFilename() !== $filename) {
            throw new NotFoundHttpException('Document non trouvé');
        }

        $this->documentManager->supprimer($document);

        return JsonResponse::fromJsonString('', Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}/{filename}', name: 'document_download', methods: ['GET'])]
    public function download(#[MapEntity(id: 'id')] Document $document, string $filename, Request $request): Response
    {
        if ($document->getFilename() !== $filename) {
            throw new NotFoundHttpException('Document non trouvé');
        }

        try {
            $stream = $this->documentManager->getContenuRessource($document);

            return new StreamedResponse(
                function () use ($stream) {
                    while (!feof($stream)) {
                        echo fread($stream, 8192);
                        flush();
                    }

                    fclose($stream);
                },
                200,
                [
                    'Content-Type' => $document->getMime() ?? 'application/octet-stream',
                    'Content-Disposition' => sprintf('%sfilename="%s"', $request->query->has('download') ? 'attachment;' : '', mb_convert_encoding($document->getOriginalFilename(), 'ISO-8859-1', 'UTF-8')),
                ]
            );
        } catch (FileException $e) {
            $this->logger->warning('Fichier de pièce jointe introuvable', ['id' => $document->getId(), 'erreur' => $e->getMessage()]);

            return new Response('', Response::HTTP_NOT_FOUND);
        }
    }
}
