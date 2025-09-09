<?php

namespace MonIndemnisationJustice\Entity;

enum QualiteRequerant: string
{
    case PRO = 'PRO';
    case LOC = 'LOC';
    case BAI = 'BAI';
    case AUT = 'AUT';

    public function getLibelle(): string
    {
        return match ($this) {
            QualiteRequerant::PRO => 'Propriétaire occupant',
            QualiteRequerant::LOC => 'Locataire',
            QualiteRequerant::BAI => 'Propriétaire bailleur',
            QualiteRequerant::AUT => 'Autre',
        };
    }
}
