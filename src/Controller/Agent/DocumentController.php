<?php

namespace MonIndemnisationJustice\Controller\Agent;

use AsyncAws\S3\Exception\NoSuchKeyException;
use Doctrine\ORM\EntityManagerInterface;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToReadFile;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Entity\TypeInstitutionSecuritePublique;
use MonIndemnisationJustice\Service\ImprimanteCourrier;
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
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

#[IsGranted(Agent::ROLE_AGENT_DOSSIER)]
#[Route('/agent/document')]
class DocumentController extends AbstractController
{
    public function __construct(
        #[Target('default.storage')]
        protected readonly FilesystemOperator $storage,
        protected readonly EntityManagerInterface $em,
    ) {
    }

    #[Route('/{id}/meta-donnees', name: 'agent_document_metadonnees', methods: ['PUT', 'PATCH'])]
    public function metaDonnees(#[MapEntity(id: 'id')] Document $document, Request $request): Response
    {
        if (DocumentType::TYPE_ATTESTATION_INFORMATION === $document->getType()) {
            $document->setMetaDonnees(
                array_merge(
                    $request->getPayload()->has('estAttestation') ? [
                        'estAttestation' => $request->getPayload()->getBoolean('estAttestation'),
                    ] : [],
                    $request->getPayload()->has('typeInstitutionSecuritePublique') ? [
                        'typeInstitutionSecuritePublique' => TypeInstitutionSecuritePublique::tryFrom(
                            $request->getPayload()->getString('typeInstitutionSecuritePublique')
                        ),
                    ] : []
                ),

                'PATCH' === $request->getMethod()
            );
            $this->em->persist($document);
            $this->em->flush();

            // En cas de mise à jour de la méta-donnée "estAttestation", recalculer l'info pour le dossier :
            if ($request->getPayload()->has('estAttestation')) {
                $em = $this->em;
                $document->getDossiers()->map(function (BrisPorte $brisPorte) use ($em) {
                    $brisPorte->recalculerEstLieAttestation();
                    $em->persist($brisPorte);
                });
            }
            $this->em->flush();
        }

        return new JsonResponse(null, Response::HTTP_NO_CONTENT);
    }

    #[Route('/{id}/{hash}', name: 'agent_document_download', methods: ['GET'])]
    public function download(#[MapEntity(id: 'id')] Document $document, string $hash, Request $request): Response
    {
        if (md5($document->getFilename()) !== $hash) {
            throw new NotFoundHttpException('Document inconnu');
        }

        try {
            if (!$this->storage->has(addslashes($document->getFilename()))) {
                throw new NotFoundHttpException('Document non trouvé');
            }

            $stream = $this->storage->readStream(addslashes($document->getFilename()));

            return new StreamedResponse(
                function () use ($stream) {
                    fpassthru($stream);
                    exit;
                },
                200,
                [
                    'Content-Transfer-Encoding', 'binary',
                    'Content-Type' => $document->getMime() ?? 'application/octet-stream',
                    'Content-Disposition' => sprintf('%sfilename="%s"', $request->query->has('download') ? 'attachment;' : '', $document->getOriginalFilename()),
                    'Content-Length' => fstat($stream)['size'],
                ]
            );
        } catch (UnableToReadFile|FilesystemException|NoSuchKeyException $e) {
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

    #[Route('/{id}/imprimer', name: 'agent_document_imprimer', methods: ['PUT'])]
    public function imprimer(#[MapEntity(id: 'id')] Document $document, Request $request, NormalizerInterface $normalizer, ImprimanteCourrier $imprimanteCourrier): Response
    {
        if (!$document->estEditable()) {
            return new JsonResponse([
                'error' => 'Ce document ne peut être édité',
            ],
                Response::HTTP_BAD_REQUEST
            );
        }

        $document->setCorps($request->getPayload()->get('corps'));

        $document = $imprimanteCourrier->imprimerDocument($document);
        $this->em->persist($document);
        $this->em->flush();

        return new JsonResponse($normalizer->normalize($document, 'json', ['groups' => ['agent:detail']]));
    }
}
