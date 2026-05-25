<?php

namespace MonIndemnisationJustice\Service;

class MontantAfficheur
{
    protected \NumberFormatter $formateur;

    public function __construct()
    {
        $this->formateur = new \NumberFormatter('fr', \NumberFormatter::SPELLOUT);
    }

    public function afficherMontantLitteral(float $montant): string
    {
        if (round($montant) === $montant) {
            return str_replace(['$montant', '$s'], [$this->formateur->format(round($montant)), $montant > 0 ? 's' : ''], '$montant euro$s');
        }


        $numberParsing = explode('.', number_format(round($montant, 2, PHP_ROUND_HALF_DOWN), 2, '.', ''));
        $_1 = $this->formateur->format($numberParsing[0]);
        $_2 = $this->formateur->format($numberParsing[1]);

        return str_replace(['$1', '$2', '$3'], [$_1, $_2, $numberParsing[1] > 1 ? 's' : ''], '$1 euros et $2 centime$3');
    }
}
