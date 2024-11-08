<?php

namespace App\Dto;

use App\Entity\GeoDepartement;

class TestEligibilite
{
    public ?GeoDepartement $departement = null;
    public bool $estVise = false;
    public ?bool $estRecherche = null;
    public ?bool $estProprietaire = null;
    public ?bool $aContacteAssurance = null;
    public ?bool $aContacteBailleur = null;

    public function toArray(): array
    {
        return [
            'departement' => $this->departement->getCode(),
            'estVise' => $this->estVise,
            'estRecherche' => $this->estRecherche,
            'estProprietaire' => $this->estProprietaire,
            'aContacteAssurance' => $this->aContacteAssurance,
            'aContacteBailleur' => $this->aContacteBailleur,
        ];
    }
}