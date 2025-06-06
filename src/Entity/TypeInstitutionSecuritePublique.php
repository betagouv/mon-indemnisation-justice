<?php

namespace MonIndemnisationJustice\Entity;

enum TypeInstitutionSecuritePublique: string
{
    case P = 'PN';
    case G = 'GN';
    case PP = 'PP';

    public function getLibelleCourt(): string
    {
        return match ($this) {
            TypeInstitutionSecuritePublique::P => 'Police',
            TypeInstitutionSecuritePublique::G => 'Gendarmerie',
            TypeInstitutionSecuritePublique::PP => 'Préfecture de Police',
        };
    }

    public function getLibelle(): string
    {
        return match ($this) {
            TypeInstitutionSecuritePublique::P => 'Police nationale',
            TypeInstitutionSecuritePublique::G => 'Gendarmerie nationale',
            TypeInstitutionSecuritePublique::PP => 'Préfecture de Police',
        };
    }
}
