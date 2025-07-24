<?php

namespace MonIndemnisationJustice\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class MotDePasseOublieDto
{
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Email]
    public ?string $email;
}
