<?php

namespace App\Controller;

use Aws\S3\S3Client;
use App\Entity\Document;
use App\Entity\LiasseDocumentaire;
use Doctrine\ORM\EntityManagerInterface;
use FOPG\Component\UtilsBundle\Env\Env;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Vich\UploaderBundle\Handler\DownloadHandler;

class DocumentController extends AbstractController
{
    public function __construct(
      private EntityManagerInterface $em,
      private DownloadHandler $downloadHandler,
      private S3Client $client
    )
    {

    }

    #[Route('/document/{id}/{type}', name: 'app_document_upload',methods: ['POST'],options: ['expose' => true])]
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
        'originalFilename' => $document->getOriginalFilename(),
      ]);
    }

    /**
     * @author yanroussel
     * @todo   Download à refaire proprement
     */
    #[Route('/document/{id}/{filename}', name: 'app_document_download',methods: ['GET'],options: ['expose' => true])]
    public function download(Request $request, ?Document $document): BinaryFileResponse
    {
      $file = $this->client->getObject([
          'Bucket' => Env::get('SCW_BUCKET'),
          'Key' => $document->getFilename(),
      ]);
      $body = $file->get('Body');
      $body->rewind();
      $tmpFilename = sys_get_temp_dir().'/'.$document->getFilename();
      $tmpContent = $body->getContents();
      file_put_contents($tmpFilename, $tmpContent);
      return new BinaryFileResponse($tmpFilename);
      if($document->getFilename() === $request->get('filename'))
        return $this->downloadHandler->downloadObject(object: $document, field:'file');
      throw new NotFoundHttpException('Document non trouvé');
    }
}
