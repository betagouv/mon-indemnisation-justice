<?php

namespace MonIndemnisationJustice\Controller\Agent;

use League\Flysystem\FilesystemException;
use League\Flysystem\UnableToReadFile;
use MonIndemnisationJustice\Api\Agent\Fip6\Voter\DossierVoter;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Service\DocumentManager;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(Agent::ROLE_AGENT_DOSSIER)]
#[Route('/agent/document')]
class DocumentController extends AbstractController
{
    public function __construct(
        protected readonly DocumentManager $documentManager,
        protected readonly LoggerInterface $logger,
    ) {
    }

    #[Route('/{id}/{hash}', name: 'agent_document_download', methods: ['GET'])]
    public function download(#[MapEntity] Document $document, string $hash, Request $request): Response
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

        $this->documentManager->supprimer($document);

        return JsonResponse::fromJsonString('', Response::HTTP_NO_CONTENT);
    }

    #[IsGranted(DossierVoter::ACTION_TRANSMETTRE_A_FIP3, subject: 'dossier', message: 'Seul un agent habilité peut transmettre le dossier', statusCode: Response::HTTP_FORBIDDEN)]
    #[Route('/dossier/{id}/documents-a-transmettre', name: 'agent_document_telecharger_a_transmettre_a_fip3', methods: ['GET'])]
    public function documentsATransmettreAFIP3(#[MapEntity] Dossier $dossier, Request $request): Response
    {
        $zip = new \ZipArchive();
        $zipName = tempnam(sys_get_temp_dir(), "zip_dossier_{$dossier->getId()}");

        if (true !== $zip->open($zipName, \ZipArchive::CREATE)) {
            throw new \RuntimeException('Cannot open '.$zipName);
        }

        /** @var Document $document */
        foreach ($dossier->getDocumentsATransmettre()->toArray() as $document) {
            try {
                $contenu = $this->documentManager->getContenuTexte($document);
                $zip->addFromString(preg_replace('#/#', '', $document->getOriginalFilename()), $contenu);
            } catch (FilesystemException|UnableToReadFile $e) {
                $this->logger->warning('Fichier de pièce jointe introuvable', ['id' => $document->getId(), 'erreur' => $e->getMessage()]);
                // $this->documentManager->supprimer($document);
            }
        }

        $zip->close();

        return new BinaryFileResponse($zipName, headers: [
            'Content-Type' => 'application/zip',
            'Content-Length' => filesize($zipName),
        ])
            ->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, preg_replace('/\//', '', "Dossier {$dossier->getReference()}.zip"));
    }
}
