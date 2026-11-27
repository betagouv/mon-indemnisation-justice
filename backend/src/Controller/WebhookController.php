<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Controller;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Message\EvenementSignatureRequerant;
use MonIndemnisationJustice\Service\CollecteurEvenementBrevo;
use MonIndemnisationJustice\Service\Signature\Prestataire\Requerant\PrestataireSignatureRequerantInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/webhooks')]
class WebhookController extends AbstractController
{
    #[Route('/brevo/evenement', name: 'webhook_brevo_evenement', methods: ['GET', 'POST'])]
    /**
     * Permet de s'abonner à un évènement d'email depuis Brevo.
     */
    public function evenementBrevo(
        CollecteurEvenementBrevo $collecteurEvenementBrevo,
        Request $request,
        EntityManagerInterface $em,
    ): Response {
        if ($collecteurEvenementBrevo->estActif()) {
            if (null !== ($evenement = $collecteurEvenementBrevo->collecter($request))) {
                $em->persist($evenement);
                $em->flush();
            }
        }

        return new Response(status: Response::HTTP_NO_CONTENT);
    }

    #[Route('/signature/evenement/{hash?}', requirements: ['hash' => '[0-9a-f]{0,64}'], defaults: ['hash' => ''])]
    public function evenementSignatureRequerant(
        Request $request,
        string $hash,
        MessageBusInterface $bus,
        PrestataireSignatureRequerantInterface $gestionnaireSignatureRequerant,
    ): Response {

        if ($gestionnaireSignatureRequerant->verifierRequeteWebhook($request, $hash)) {
            if (is_array($donnees = json_decode($request->getContent(), true))) {
                $bus->dispatch(new EvenementSignatureRequerant($donnees));
            }
        }

        return new JsonResponse('', Response::HTTP_NO_CONTENT);
    }
}
