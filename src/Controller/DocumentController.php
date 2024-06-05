<?php

namespace App\Controller;

use App\Entity\Document;
use App\Entity\LiasseDocumentaire;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Vich\UploaderBundle\Handler\DownloadHandler;

class DocumentController extends AbstractController
{
    public function __construct(
      private EntityManagerInterface $em,
      private DownloadHandler $downloadHandler
    )
    {

    }

    #[Route('/{id}/{type}/document-upload', name: 'app_document_upload',methods: ['POST'],options: ['expose' => true])]
    public function upload(LiasseDocumentaire $liasseDocumentaire, Request $request): JsonResponse
    {
      $files = $request->files->all();
      /** @var UploadedFile $file */
      foreach($files as $file) {
        $document = new Document();
        $document->setFile($file);
        $document->setFilename($file->getClientOriginalName());
        $document->setOriginalFilename($file->getClientOriginalName());
        $document->setSize($file->getSize());
        $document->setType($request->get('type'));
        $document->setLiasseDocumentaire($liasseDocumentaire);
        $this->em->persist($document);
        $this->em->flush();
      }
      return new JsonResponse([
        'id' => $document->getId(),
        'filename' => $document->getFilename(),
        'size' => $document->getSize(),
        'type' => $document->getType(),
      ]);
    }

    #[Route('/{id}/document-download', name: 'app_document_download',methods: ['GET'],options: ['expose' => true])]
    public function download(?Document $document): StreamedResponse
    {
      return $this->downloadHandler->downloadObject($document, $fileField = 'file');
    }
}
