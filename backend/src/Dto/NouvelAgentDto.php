<?php

namespace MonIndemnisationJustice\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class NouvelAgentDto
{
    public string $prenom = '';
    public string $nom = '';
    #[Assert\NotNull]
    #[Assert\Email]
    public ?string $courriel = null;
}
