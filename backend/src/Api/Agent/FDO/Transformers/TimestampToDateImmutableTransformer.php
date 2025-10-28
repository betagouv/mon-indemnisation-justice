<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Transformers;

use Symfony\Component\ObjectMapper\TransformCallableInterface;

class TimestampToDateImmutableTransformer implements TransformCallableInterface
{
    public function __invoke(mixed $value, object $source, ?object $target): ?\DateTimeImmutable
    {
        if (is_int($value)) {
            return \DateTimeImmutable::createFromFormat('U', intdiv($value, 100));
        }

        return null;
    }
}
