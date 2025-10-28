<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Transformers;

use Symfony\Component\ObjectMapper\TransformCallableInterface;

class TimestampToDateTransformer implements TransformCallableInterface
{
    public function __invoke(mixed $value, object $source, ?object $target): ?\DateTime
    {
        if (is_int($value)) {
            return \DateTime::createFromFormat('U', intdiv($value, 100));
        }

        return null;
    }
}
