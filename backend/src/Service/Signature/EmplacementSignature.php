<?php

namespace MonIndemnisationJustice\Service\Signature;

/**
 * Emplacement du visuel de signature sur le document, en points PDF (1/72 de pouce).
 */
final readonly class EmplacementSignature
{
    /**
     * Hauteur d'une page A4, en points PDF : repli pour les documents imprimés avant que la hauteur de page ne soit
     * extraite.
     */
    public const int HAUTEUR_PAGE_A4 = 842;

    public function __construct(
        private int $x,
        private int $y,
        private int $largeur,
        private int $hauteur,
        private int $numeroPage = 1,
        private int $hauteurPage = self::HAUTEUR_PAGE_A4,
    ) {
    }

    public function getX(): int
    {
        return $this->x;
    }

    public function getY(bool $origineHaut = true): int
    {
        return $origineHaut ? $this->y : $this->hauteurPage - $this->y - $this->hauteur;
    }

    public function getLargeur(): int
    {
        return $this->largeur;
    }

    public function getHauteur(): int
    {
        return $this->hauteur;
    }

    public function getNumeroPage(): int
    {
        return $this->numeroPage;
    }

    public function getHauteurPage(): int
    {
        return $this->hauteurPage;
    }
}
