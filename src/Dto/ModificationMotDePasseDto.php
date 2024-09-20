<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ModificationMotDePasseDto
{

    public string $email;

    public string $motDePasse;
    public string $confirmation;
}
