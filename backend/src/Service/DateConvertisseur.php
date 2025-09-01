<?php

namespace MonIndemnisationJustice\Service;

class DateConvertisseur
{
    public static function enMillisecondes(?\DateTimeInterface $date = null): ?int
    {
        return $date ? $date->getTimestamp() * 1000 : null;
    }
}
