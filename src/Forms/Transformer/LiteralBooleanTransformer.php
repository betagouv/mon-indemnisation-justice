<?php

namespace MonIndemnisationJustice\Forms\Transformer;

use Symfony\Component\Form\DataTransformerInterface;

/**
 * Permet de manipuler des booléens exprimé en _plain javascript_.
 */
class LiteralBooleanTransformer implements DataTransformerInterface
{
    public function transform(mixed $value): ?string
    {
        if (null === $value) {
            return null;
        }

        return $value ? 'true' : 'false';
    }

    public function reverseTransform(mixed $value): ?bool
    {
        return $value ? filter_var($value, FILTER_VALIDATE_BOOL) : null;
    }
}
