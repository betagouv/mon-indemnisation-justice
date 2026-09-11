<?php

namespace MonIndemnisationJustice\Service;

use MonIndemnisationJustice\Entity\Document;

/**
 * Levée par {@see FusionneurDocuments::fusionner()} lorsqu'un {@see Document} particulier empêche la fusion,
 * afin de permettre à l'appelant de retenter la fusion en l'excluant de la liste.
 */
class FusionDocumentException extends \RuntimeException
{
    public function __construct(
        private readonly Document $document,
        string $message,
        ?\Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function getDocument(): Document
    {
        return $this->document;
    }
}
