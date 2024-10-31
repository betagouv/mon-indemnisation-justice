<?php

namespace App\Dto;

class TestEligibilite
{
    public bool $estVise = false;
    public ?bool $estRecherche = null;
    public ?bool $estProprietaire = null;
    public ?bool $aContacteAssurance = null;
    public ?bool $aContacteBailleur = null;

    public function toArray(): array
    {
        return [
            'estVise' => $this->estVise,
            'estRecherche' => $this->estRecherche,
            'estProprietaire' => $this->estProprietaire,
            'aContacteAssurance' => $this->aContacteAssurance,
            'aContacteBailleur' => $this->aContacteBailleur,
        ];
    }
}