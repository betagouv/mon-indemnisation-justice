<?php

namespace MonIndemnisationJustice\Controller\Agent;

use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\LiasseDocumentaire;
use MonIndemnisationJustice\Entity\Requerant;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

//#[IsGranted(Agent::ROLE_AGENT_REDACTEUR)]
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
