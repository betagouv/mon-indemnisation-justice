<?php

namespace MonIndemnisationJustice\Api\Requerant\Dossier\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class ContexteSignatureElectroniqueDto
{
    #[Assert\NotBlank(message: "Le choix du relevé d'identité bancaire est requis")]
    public string $rib;
}
