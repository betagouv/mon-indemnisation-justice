<?php

namespace MonIndemnisationJustice\Service\Signature;

use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\RequeteSignatureRequerant;
use Symfony\Component\HttpFoundation\Request;

interface GestionnaireSignatureRequerantInterface
{
    /**
     * Initie une requête de signature électronique pour le requérant et retourne l'entité créée.
     */
    public function creerRequeteSignature(Dossier $dossier): RequeteSignatureRequerant;

    /**
     * Établit si la requête de webhook est la dernière tentative pour l'évènement associé.
     */
    public function estDerniereTentative(Request $request): bool;

    /**
     * Vérifier la véracité de la requête de webhook.
     */
    public function verifierRequeteWebhook(Request $request): bool;

    /**
     * Extrait une requête de signature depuis les données envoyées au webhook et la modifie en conséquence. En cas de modification,
     * l'entité doit être renvoyée. Si aucune entité n'est reconnue ou qu'elle n'est pas modifiée, cette méthode doit
     * retourner `null`.
     */
    public function gererWebhookEvenementSignature(array $donnees): ?RequeteSignatureRequerant;
}
