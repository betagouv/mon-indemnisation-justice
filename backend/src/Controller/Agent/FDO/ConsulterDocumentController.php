<?php

namespace MonIndemnisationJustice\Controller\Agent\FDO;

use League\Flysystem\FilesystemException;
use League\Flysystem\UnableToReadFile;
use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Security\Voter\FDODocumentVoter;
use MonIndemnisationJustice\Service\DocumentManager;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(
    FDODocumentVoter::ACTION_CONSULTER,
    'document',
    message: "L'accès à ce document est réservé à l'agent des Forces de l'Ordre",
    statusCode: Response::HTTP_FORBIDDEN
)]
#[Route('/agent/fdo/document/{id}/{hash}', methods: ['GET'])]
class ConsulterDocumentController
{
    public function __construct(
        protected readonly DocumentManager $documentManager,
        protected readonly LoggerInterface $logger,
    ) {}

    public function __invoke(#[MapEntity] Document $document, string $hash, Request $request): Response
    {
        if (md5($document->getFilename()) !== $hash) {
            throw new NotFoundHttpException('Document inconnu');
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
        } catch (FilesystemException|UnableToReadFile $e) {
            $this->logger->warning('Fichier de pièce jointe introuvable', ['id' => $document->getId(), 'erreur' => $e->getMessage()]);

            return new Response('', Response::HTTP_NOT_FOUND);
        }
    }
}
