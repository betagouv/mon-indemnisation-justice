<?php

namespace MonIndemnisationJustice\Entity;

use MonIndemnisationJustice\Api\Requerant\Brouillon\Dto\DossierDto;

enum BrouillonType: string
{
    case BROUILLON_DOSSIER_BRIS_PORTE = 'BROUILLON_DOSSIER_BRIS_PORTE';

    public function getLibelle(): string
    {
        return match ($this) {
            self::BROUILLON_DOSSIER_BRIS_PORTE => 'Bris de porte',
        };
    }

    /**
     * Retourne la classe utilisée pour modéliser le brouillon en cours.
     */
    public function getClasseTravail(): string
    {
        return match ($this) {
            self::BROUILLON_DOSSIER_BRIS_PORTE => DossierDto::class,
        };
    }

    /**
     * Retourne la classe utilisée pour engendrer une entité lors de la publication.
     */
    public function getClassePublication(): string
    {
        return match ($this) {
            self::BROUILLON_DOSSIER_BRIS_PORTE => Dossier::class,
        };
    }

    /**
     * Permet d'enrichir les données brutes avec les informations du contexte du brouillon.
     */
    public function enrichirDonneesAvecContexteBrouillon(array $donnees, Brouillon $brouillon): array
    {
        return match ($this) {
            self::BROUILLON_DOSSIER_BRIS_PORTE => array_merge(
                $donnees,
                [
                    'reference' => $brouillon->getId()->toString(),
                    'usager' => $brouillon->getUsager()->getId(),
                    'etatActuel' => [
                        'etat' => 'A_COMPLETER',
                        'date' => $brouillon->getDateCreation()->format('Y-m-d'),
                        'requerant' => [
                            'id' => $brouillon->getUsager()->getId(),
                            'nom' => $brouillon->getUsager()->getPersonne()->getNomCourant(capital: true),
                        ],
                    ],
                ]
            ),
            default => $donnees,
        };
    }

    /**
     * Permet d'ôter les données de contexte du brouillon avant qu'elles ne soient persistées.
     */
    public function filtrerDonneesSansContexteBrouillon(array $donnees, Brouillon $brouillon): array
    {
        return match ($this) {
            self::BROUILLON_DOSSIER_BRIS_PORTE => array_filter($donnees, fn ($key) => !in_array($key, ['reference', 'usager', 'etatActuel']), ARRAY_FILTER_USE_KEY),
            default => $donnees,
        };
    }
}
