<?php

namespace MonIndemnisationJustice\Api\Requerant\Brouillon\Dto;

class EtatDossierDto
{
    public \DateTimeImmutable $date;
    public ?object $agent;
    public ?object $requerant;
    public ?array $contexte = null;
}