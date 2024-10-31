<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

class MotDePasseOublieDto
{
    public function __construct(
        #[Assert\NotNull]
        #[Assert\NotBlank]
        #[Assert\Email]
        public readonly string $email,
    ) {
    }
}
