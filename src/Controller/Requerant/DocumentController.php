<?php

namespace App\Controller\Requerant;

use App\Entity\Document;
use App\Entity\LiasseDocumentaire;
use App\Entity\Requerant;
use Aws\S3\S3Client;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Vich\UploaderBundle\Handler\DownloadHandler;


#[IsGranted(Requerant::ROLE_REQUERANT)]
#[Route('/document')]
class DocumentController extends AbstractController
{
    public function __construct(
      private EntityManagerInterface $em,
      private DownloadHandler $downloadHandler,
      private S3Client $client,
        protected readonly string $bucket
    )
    {
    }

    #[Route('/{id}/{type}', name: 'app_document_upload',methods: ['POST'],options: ['expose' => true])]
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
    #[Route('/{id}/{filename}', name: 'app_document_download',methods: ['GET'],options: ['expose' => true])]
    public function download(Request $request, ?Document $document): BinaryFileResponse
    {
      $file = $this->client->getObject([
          'Bucket' => $this->bucket,
          'Key' => $document->getFilename(),
      ]);
      $body = $file->get('Body');
      $body->rewind();

      $tmpFilename = sys_get_temp_dir().'/'.$document->getFilename();
      $tmpContent = $body->getContents();
      file_put_contents($tmpFilename, $tmpContent);
      $response = new BinaryFileResponse($tmpFilename);
      $response->setContentDisposition(
        ResponseHeaderBag::DISPOSITION_ATTACHMENT,
        $document->getOriginalFilename()
      );
      return $response;

      if($document->getFilename() === $request->get('filename'))
        return $this->downloadHandler->downloadObject(object: $document, field:'file');
      throw new NotFoundHttpException('Document non trouvé');
    }
}
