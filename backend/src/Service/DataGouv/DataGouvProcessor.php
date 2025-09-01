<?php

namespace MonIndemnisationJustice\Service\DataGouv;

interface DataGouvProcessor
{
    public function getResource(): string;

    public function processRecord(array $record): void;

    public function onProcessed(): void;
}
