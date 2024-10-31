<?php

namespace App\Validation\Validator;

use App\Repository\RequerantRepository;
use App\Validation\Constraint\UniqueRequerantCourriel;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueRequerantCourrielValidator extends ConstraintValidator
{
    public function __construct(
        protected readonly RequerantRepository $requerantRepository,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueRequerantCourriel) {
            throw new UnexpectedTypeException($constraint, UniqueRequerantCourriel::class);
        }

        if ($value) {
            if ($this->requerantRepository->count(['email' => $value]) > 0) {
                $this->context
                    ->buildViolation($constraint->message)
                    ->addViolation();
            }
        }
    }
}
