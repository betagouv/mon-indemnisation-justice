<?php

namespace App\Dto;

class TestEligibilite
{
    public \DateTime $dateOperationPJ;
    public bool $estVise;
    public ?bool $estRecherche;
    public ?bool $estProprietaire;
    public ?bool $aContacteAssurance;
    public ?bool $aContacteBailleur;

    public function toArray(): array
    {
        return [
            'dateOperationPJ' => $this->dateOperationPJ,
            'estVise' => $this->estVise,
            'estRecherche' => $this->estRecherche,
            'estProprietaire' => $this->estProprietaire,
            'aContacteAssurance' => $this->aContacteAssurance,
            'aContacteBailleur' => $this->aContacteBailleur,
        ];
    }
}