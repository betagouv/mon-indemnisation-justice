<?php

namespace MonIndemnisationJustice\Entity\Metadonnees;

/**
 * Une classe de Métadonnées a pour objectif de modéliser les données qui sont stockées dans un champ de type JSON.
 *
 * Chaque sous classe qui l'étend doit donc définir ses champs dans le constructeur, la logique de normalisation /
 * dé-normalisation étant ensuite toujours la même.
 */
abstract readonly class AbstractMetadonnees
{
    public function versArray(): array
    {
        return array_merge(get_object_vars($this), $this->normaliser());
    }

    public static function depuisArray(array $valeurs): static
    {
        return new static(...$valeurs);
    }

    protected function normaliser(): array
    {
        return [];
    }
}
