<?php

namespace MonIndemnisationJustice\Entity;

enum QualiteRequerant: string
{
    case PRO = 'PRO';
    case LOC = 'LOC';
    case HEB = 'HEB';
    case AUT = 'AUT';

    public function getLibelle(): string
    {
        return match ($this) {
            QualiteRequerant::PRO => 'Propriétaire',
            QualiteRequerant::LOC => 'Locataire',
            QualiteRequerant::HEB => 'Hébergeant',
            QualiteRequerant::AUT => 'Autre',
        };
    }
}
