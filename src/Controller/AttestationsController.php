<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/forces-de-l-ordre')]
class AttestationsController extends AbstractController
{
    protected string $assetsDirectory;

    public function __construct(#[Autowire(param: 'kernel.project_dir')] string $projectDirectory)
    {
        $this->assetsDirectory = $projectDirectory.'/assets/';
    }

    #[Route('/attestations', name: 'attestations_index')]
    public function index(): Response
    {
        return $this->render('attestations.html.twig');
    }

    #[Route('/attestations/attestation-a-remettre-en-cas-d-erreur-de-porte', name: 'attestations_attestation_a_remettre')]
    public function attestationARemettre(): Response
    {
        return new BinaryFileResponse($this->assetsDirectory.'/attestations/attestation-a-remettre-en-cas-d-erreur-de-porte.pdf');
    }

    #[Route('/attestations/guide-de-remise-de-l-attestation', name: 'attestations_guide_de_remise')]
    public function guideDeRemiseDLattestation(): Response
    {
        return new BinaryFileResponse($this->assetsDirectory.'/attestations/guide-de-remise-de-l-attestation.pdf');
    }
}
