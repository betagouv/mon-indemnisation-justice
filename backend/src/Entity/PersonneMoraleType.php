<?php

namespace MonIndemnisationJustice\Entity;

enum PersonneMoraleType: string
{
    case ASSUREUR = 'ASSUREUR';
    case SCI = 'SCI';
    case ENTREPRISE_PRIVEE = 'ENTREPRISE_PRIVEE';
    case BAILLEUR_SOCIAL = 'BAILLEUR_SOCIAL';
    case SYNDIC = 'SYNDIC';
    case ASSOCIATION = 'ASSOCIATION';
    case COLLECTIVITE = 'COLLECTIVITE';
    case ETABLISSEMENT_PUBLIC = 'ETABLISSEMENT_PUBLIC';

    /**
     * @param bool|null $defini l'article, défini ou non, à utiliser.
     *
     * "Ex: l'assocation Machin" ou "un Syndic Truc"
     */
    public function getLibelle(?bool $defini): string
    {
        return sprintf('%s%s', $this->getArticle($defini), match ($this) {
            self::ASSOCIATION => 'association',
            self::SYNDIC => 'syndic',
            default => 'société',
        });
    }

    public function getArticle(?bool $defini): string
    {
        if (null === $defini) {
            return '';
        }

        return match ($this) {
            self::ASSOCIATION => $defini ? 'l\' ' : 'une ',
            self::SYNDIC => $defini ? 'le ' : 'un ',
            default => $defini ? 'la ' : 'une ',
        };
    }
}
