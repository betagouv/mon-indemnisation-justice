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
        return new static(static::construireArguments($valeurs));
    }

    protected static function construireArguments(array $valeurs): array
    {
        $reflectionClass = new \ReflectionClass(static::class);

        // Noms des arguments du constructeur
        $clefs = array_map(fn ($parameter) => $parameter->getName(), $reflectionClass->getConstructor()?->getParameters() ?? []);

        return array_filter(
            $valeurs,
            fn ($valeur, $clef) => in_array($clef, $clefs),
            ARRAY_FILTER_USE_BOTH
        );
    }

    protected function normaliser(): array
    {
        return [];
    }
}
