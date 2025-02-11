<?php

namespace MonIndemnisationJustice\Controller\Agent;

use League\Flysystem\FilesystemOperator;
use MonIndemnisationJustice\Entity\Document;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;

// #[IsGranted(Agent::ROLE_AGENT_REDACTEUR)]
#[Route('/agent/document')]
class DocumentController extends AbstractController
{
    public function __construct(
        #[Target('default.storage')]
        protected readonly FilesystemOperator $storage,
    ) {
    }

    #[Route('/{id}/{hash}', name: 'agent_document_download', methods: ['GET'])]
    public function download(#[MapEntity(id: 'id')] Document $document, string $hash): Response
    {
        if (md5($document->getFilename()) !== $hash) {
            throw new NotFoundHttpException('Document non trouvé');
        }

        $stream = $this->storage->readStream($document->getFilename());

        return new StreamedResponse(
            function () use ($stream) {
                fpassthru($stream);
                exit;
            },
            200,
            [
                'Content-Transfer-Encoding', 'binary',
                'Content-Type' => $document->getMime() ?? 'application/octet-stream',
                'Content-Disposition' => sprintf('attachment; filename="%s"', $document->getOriginalFilename()),
                'Content-Length' => fstat($stream)['size'],
            ]
        );
    }
}
