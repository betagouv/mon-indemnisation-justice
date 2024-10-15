<?php

namespace App\Dto;

class TestEligibilite
{
    public bool $estVise;
    public ?bool $estRecherche;
    public ?bool $estProprietaire;
    public ?bool $aContacteAssurance;
    public ?bool $aContacteBailleur;

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