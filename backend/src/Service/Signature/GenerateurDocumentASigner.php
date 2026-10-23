<?php

namespace MonIndemnisationJustice\Service\Signature;

use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Service\DocumentManager;
use setasign\Fpdi\Fpdi;
use setasign\Fpdi\PdfParser\StreamReader;

/**
 * Génère, pour les différents prestataires de signature électronique, la déclaration d'acceptation à faire signer au
 * requérant.
 */
class GenerateurDocumentASigner
{
    public function __construct(
        protected readonly DocumentManager $documentManager,
    ) {
    }

    public function genererDeclarationAcceptation(Dossier $dossier): DocumentASigner
    {
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

        $contenu = $this->documentManager->getContenuTexte($declarationAcceptation);

        return new DocumentASigner(
            $declarationAcceptation->getFilename(),
            $contenu,
            $this->compterPages($contenu),
        );
    }

    /**
     * Le parser gratuit de FPDI ne gère pas toutes les compressions de PDF (voir https://www.setasign.com/fpdi-pdf-parser) :
     * à défaut, on considère que le document ne compte qu'une page.
     */
    protected function compterPages(string $contenu): int
    {
        try {
            return max(1, new Fpdi()->setSourceFile(StreamReader::createByString($contenu)));
        } catch (\Throwable) {
            return 1;
        }
    }
}
