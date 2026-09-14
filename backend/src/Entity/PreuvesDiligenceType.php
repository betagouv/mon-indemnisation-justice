<?php

namespace MonIndemnisationJustice\Entity;

enum PreuvesDiligenceType: string
{
    case AVEC_JUSTIFICATIFS = 'avec_justificatifs';
    case SANS_JUSTIFICATIF = 'sans_justificatif';
    case PAS_DE_DEMARCHE = 'pas_de_demarche';

    public function estRecevable(): bool
    {
        return self::PAS_DE_DEMARCHE !== $this;
    }
}
