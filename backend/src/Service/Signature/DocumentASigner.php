<?php

namespace MonIndemnisationJustice\Service\Signature;

/**
 * Document PDF généré pour être soumis à la signature électronique du requérant.
 */
final readonly class DocumentASigner
{
    public function __construct(
        public string $nomFichier,
        public string $contenu,
        public int $nombrePages,
    ) {
    }
}
