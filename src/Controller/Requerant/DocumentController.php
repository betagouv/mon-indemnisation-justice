<?php

namespace App\Controller\Requerant;

use App\Entity\Document;
use App\Entity\LiasseDocumentaire;
use App\Entity\Requerant;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemOperator;
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

#[IsGranted(Requerant::ROLE_REQUERANT)]
#[Route('/document')]
class DocumentController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $em,
        #[Target('default.storage')]
        protected readonly FilesystemOperator $storage,
    ) {
    }

    #[Route('/{id}/{type}', name: 'app_document_upload', methods: ['POST'], options: ['expose' => true])]
    public function upload(LiasseDocumentaire $liasseDocumentaire, Request $request, string $type): JsonResponse
    {
        $files = $request->files->all();

        /** @var UploadedFile $file */
        foreach ($files as $file) {
            $content = $file->getContent();
            $filename = hash('sha256', $content).'.'.($file->guessExtension() ?? $file->getExtension());
            $this->storage->write($filename, $content);
            $document = (new Document())
                ->setFilename($filename)
                ->setOriginalFilename($file->getClientOriginalName())
                ->setSize($file->getSize())
                ->setType($type)
                ->setMime($file->getMimeType())
                ->setLiasseDocumentaire($liasseDocumentaire);
            $this->em->persist($document);
        }

        $this->em->flush();

        return new JsonResponse([
            'id' => $document->getId(),
            'filename' => $document->getFilename(),
            'size' => $document->getSize(),
            'type' => $document->getType(),
            'originalFilename' => $document->getOriginalFilename(),
        ]);
    }

    #[Route('/{id}/{filename}', name: 'app_document_remove', methods: ['DELETE'])]
    public function delete(Document $document, string $filename): Response
    {
        if ($document->getFilename() !== $filename) {
            throw new NotFoundHttpException('Document non trouvé');
        }

        $this->storage->delete($document->getFilename());

        $this->em->remove($document);
        $this->em->flush();

        return JsonResponse::fromJsonString('', Response::HTTP_NO_CONTENT);
    }

    /**
     * @author yanroussel
     *
     * @todo   Download à refaire proprement
     */
    #[Route('/{id}/{filename}', name: 'app_document_download', methods: ['GET'], options: ['expose' => true])]
    public function download(Document $document, string $filename): Response
    {
        if ($document->getFilename() !== $filename) {
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
