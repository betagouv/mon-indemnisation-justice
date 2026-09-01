<?php

namespace MonIndemnisationJustice\Validation\Validator;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\Avocat;
use MonIndemnisationJustice\Validation\Constraint\AvocatConnu;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AvocatConnuValidator extends ConstraintValidator
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof AvocatConnu) {
            throw new UnexpectedTypeException($constraint, AvocatConnu::class);
        }

        // NotBlank/Regex gèrent déjà les valeurs vides ou mal formées : inutile d'aller taper la base pour elles.
        if (!\is_string($value) || !preg_match('/^\d{6}$/', $value)) {
            return;
        }

        if (null === $this->em->getRepository(Avocat::class)->find($value)) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
