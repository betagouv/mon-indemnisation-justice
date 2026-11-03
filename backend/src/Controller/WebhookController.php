<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Controller;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Service\CollecteurEvenementBrevo;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/webhook')]
class WebhookController extends AbstractController
{
    #[Route('/brevo/evenement', name: 'webhook_brevo_evenement', methods: ['POST'], condition: "est_fonctionnalite_active('brevo')")]
    /**
     * Permet de s'abonner à un évènement d'email depuis Brevo.
     */
    public function evenementBrevo(CollecteurEvenementBrevo $collecteurEvenementBrevo, Request $request, EntityManagerInterface $em): Response
    {
        if ($collecteurEvenementBrevo->estActif()) {
            if (null !== ($evenement = $collecteurEvenementBrevo->collecter($request))) {
                // TODO sauvegarder l'évènement
                $em->persist($evenement);
                $em->flush();
            }
        }

        return new Response(status: Response::HTTP_NO_CONTENT);
    }
}
