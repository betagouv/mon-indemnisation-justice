<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/forces-de-l-ordre')]
// TODO supprimer ce controller dès lors que la page n'est plus consultée
class AttestationsController extends AbstractController
{
    #[Route('/attestations', name: 'attestations_index')]
    public function index(): Response
    {
        return $this->render('attestations.html.twig');
    }
}
