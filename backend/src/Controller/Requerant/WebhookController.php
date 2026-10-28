<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Controller\Requerant;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Service\Signature\GestionnaireSignatureRequerantInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/webhooks')]
class WebhookController extends AbstractController
{
    public function __construct(
        protected readonly LoggerInterface $logger,
    ) {

    }

    #[Route('/signature/evenement')]
    public function evenementSignatureRequerant(
        Request $request,
        GestionnaireSignatureRequerantInterface $gestionnaireSignatureRequerant,
        EntityManagerInterface $em,
    ): Response {
        dump($request->headers->all());
        dump($request->getContent());

        if ($gestionnaireSignatureRequerant->verifierRequeteWebhook($request)) {
            if (is_array($donnees = json_decode($request->getContent(), true))) {
                try {
                    $requeteSignature = $gestionnaireSignatureRequerant->gererWebhookEvenementSignature($donnees);

                    if (null !== $requeteSignature) {
                        $em->persist($requeteSignature);
                        $em->flush();
                    }
                } catch (\Throwable $exception) {
                    $this->logger->error("Erreur lors du traitement du webhook d'évènement signature requérant", [
                        'erreur' => $exception->getMessage(),
                        'donnees' => $donnees,
                    ]);
                    if (!$gestionnaireSignatureRequerant->estDerniereTentative($request)) {

                        return new JsonResponse('', Response::HTTP_INTERNAL_SERVER_ERROR);
                    }

                    // TODO envoyer le message pour traitement en asynchrone
                }
            }
        }


        return new JsonResponse('', Response::HTTP_NO_CONTENT);

    }
}
