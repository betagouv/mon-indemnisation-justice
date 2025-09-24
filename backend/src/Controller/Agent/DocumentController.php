<?php

namespace MonIndemnisationJustice\Controller\Agent;

use AsyncAws\S3\Exception\NoSuchKeyException;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToReadFile;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\Document;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(Agent::ROLE_AGENT_DOSSIER)]
#[Route('/agent/document')]
class DocumentController extends AbstractController
{
    public function __construct(
        #[Target('default.storage')]
        protected readonly FilesystemOperator $storage,
        protected readonly EntityManagerInterface $em,
        protected readonly LoggerInterface $logger,
    ) {}

    #[Route('/{id}/{hash}', name: 'agent_document_download', methods: ['GET'])]
    public function download(#[MapEntity] Document $document, string $hash, Request $request): Response
    {
        if (md5($document->getFilename()) !== $hash) {
            throw new NotFoundHttpException('Document inconnu');
        }

        try {
            if (!$this->storage->has($document->getFilename())) {
                return new Response('', Response::HTTP_NOT_FOUND);
            }

            /** @var resource $stream */
            $stream = $this->storage->readStream($document->getFilename());

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
        } catch (FilesystemException|NoSuchKeyException|UnableToReadFile $e) {
            $this->logger->warning('Fichier de pièce jointe introuvable', ['id' => $document->getId()]);

            return new Response('', Response::HTTP_NOT_FOUND);
        }
    }

    #[IsGranted(
        attribute: new Expression('is_granted("ROLE_AGENT_ATTRIBUTEUR") or is_granted("ROLE_AGENT_VALIDATEUR") or user.instruit(subject["document"].getDossier())'),
        subject: [
            'document' => new Expression('args["document"]'),
        ]
    )]
    #[Route('/{id}/supprimer', name: 'agent_document_supprimer', methods: ['DELETE'])]
    public function supprimer(#[MapEntity(id: 'id')] Document $document): Response
    {
        if (false !== $document->estAjoutRequerant()) {
            return new JsonResponse([
                'error' => 'Ce document ne peut être supprimé',
            ], Response::HTTP_BAD_REQUEST);
        }

        $this->em->remove($document);
        $this->em->flush();

        return new JsonResponse('', Response::HTTP_NO_CONTENT);
    }
}
