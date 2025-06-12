<?php

namespace MonIndemnisationJustice\Entity;

enum TypeInstitutionSecuritePublique: string
{
    case PN = 'PN';
    case GN = 'GN';
    case PP = 'PP';

    public function getLibelleCourt(): string
    {
        return match ($this) {
            TypeInstitutionSecuritePublique::PN => 'Police',
            TypeInstitutionSecuritePublique::GN => 'Gendarmerie',
            TypeInstitutionSecuritePublique::PP => 'Préfecture de Police',
        };
    }

    public function getLibelle(): string
    {
        return match ($this) {
            TypeInstitutionSecuritePublique::PN => 'Police nationale',
            TypeInstitutionSecuritePublique::GN => 'Gendarmerie nationale',
            TypeInstitutionSecuritePublique::PP => 'Préfecture de Police',
        };
    }
}
