<?php

namespace MonIndemnisationJustice\Service\Signature\Prestataire\Requerant;

use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\RequeteSignatureRequerant;
use MonIndemnisationJustice\Service\Signature\DocumentASigner;
use MonIndemnisationJustice\Service\Signature\Evenement\EvenementSignature;
use Symfony\Component\HttpFoundation\Request;

interface PrestataireSignatureRequerantInterface
{
    /**
     * Initie une requête de signature électronique pour le requérant et retourne l'entité créée.
     */
    public function creerRequeteSignature(Dossier $dossier, DocumentASigner $documentASigner): RequeteSignatureRequerant;

    /**
     * Vérifier la véracité de la requête de webhook.
     */
    public function verifierRequeteWebhook(Request $request, string $hash = ''): bool;

    /**
     * Extrait un évènement de signature depuis des données reçues via webhook.
     */
    public function extraireEvenementSignatureWebhook(array $donnees): ?EvenementSignature;

    /**
     * Récupère le document auprès du prestataire une fois celui-ci dûment signé.
     */
    public function telechargerDocumentSigne(RequeteSignatureRequerant $requeteSignatureRequerant): ?string;
}
