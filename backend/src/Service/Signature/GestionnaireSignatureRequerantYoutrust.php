<?php

namespace MonIndemnisationJustice\Service\Signature;

use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Service\DocumentManager;
use MonIndemnisationJustice\Service\Signature\Youtrust\YoutrustClient;

class GestionnaireSignatureRequerantYoutrust implements GestionnaireSignatureRequerantInterface
{
    public function __construct(
        protected readonly DocumentManager $documentManager,
        protected readonly YoutrustClient $youtrustClient,
    ) {

    }

    public function creerRequeteSignature(Dossier $dossier): void
    {
        // 1. Générer la déclaration d'acceptation en mode signature
        $declarationAcceptation = $this->documentManager
            ->generer(
                $dossier,
                DocumentType::TYPE_FORMULAIRE_ACCEPTATION,
                montantIndemnisation: $dossier->getMontantIndemnisation(),
                contexte: [
                    'signature' => [
                        'date' => new \DateTimeImmutable(),
                        'lieu' => $dossier->getRequerantPersonnePhysique()?->getAdresse()?->getLocalite() ?? $dossier->getRequerantPersonneMorale()?->getAdresse()?->getLocalite() ?? $dossier->getBrisPorte()->getAdresse()->getLocalite() ?? '',
                        'rib' => [
                            'type' => 'personnel',
                        ],
                    ],
                ]
            );

        // 2. Créer la requête de signature
        $requete = $this->youtrustClient->initiateSignatureRequest("Acceptation de l'indemnisation {$dossier->getReference()}");

        // 3. Téléverser le document
        $requete = $this->youtrustClient->uploadDocument($requete, $declarationAcceptation);

        // 4. Déclarer le requérant comme signataire

        // 5. Activer la requête de signature
    }
}
