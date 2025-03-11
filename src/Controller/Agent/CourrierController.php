<?php

namespace MonIndemnisationJustice\Controller\Agent;

use AsyncAws\S3\Exception\NoSuchKeyException;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use League\Flysystem\UnableToReadFile;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\BrisPorte;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

// #[IsGranted(Agent::ROLE_AGENT_REDACTEUR)]
#[Route('/agent/redacteur')]
class CourrierController extends AbstractController
{
    public function __construct(
        #[Target('default.storage')]
        protected readonly FilesystemOperator $storage,
    ) {
    }

    #[IsGranted(Agent::ROLE_AGENT_DOSSIER)]
    #[Route('/dossier/{id}/courrier/previsualiser', name: 'agent_redacteur_courrier_dossier_previsualiser', methods: ['GET'], condition: "env('APP_DEBUG')")]
    public function previsualiserCourrierDossier(#[MapEntity(id: 'id')] BrisPorte $dossier, Request $request): Response
    {
        return $this->render('courrier/dossier.html.twig', [
            'dossier' => $dossier,
            'web' => $request->query->getBoolean('w', true),
            'formulaire' => $request->query->getBoolean('f', true),
        ]);
    }

    #[IsGranted(Agent::ROLE_AGENT_DOSSIER)]
    #[Route('/dossier/{id}/courrier/{hash}', name: 'agent_redacteur_courrier_dossier', methods: ['GET'])]
    public function courrierDossier(#[MapEntity(id: 'id')] BrisPorte $dossier, string $hash, Request $request): Response
    {
        if (md5($dossier->getCourrier()?->getFilename()) !== $hash) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        try {
            if (!$this->storage->has($dossier->getCourrier()->getFilename())) {
                return new Response('', Response::HTTP_NOT_FOUND);
            }

            $stream = $this->storage->readStream($dossier->getCourrier()->getFilename());

            return new StreamedResponse(
                function () use ($stream) {
                    fpassthru($stream);
                    exit;
                },
                200,
                [
                    'Content-Transfer-Encoding', 'binary',
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => sprintf('attachment; filename="Courrier synthèse dossier %s"', $dossier->getReference()),
                    'Content-Length' => fstat($stream)['size'],
                ]
            );
        } catch (UnableToReadFile|FilesystemException|NoSuchKeyException $e) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }
    }
}
