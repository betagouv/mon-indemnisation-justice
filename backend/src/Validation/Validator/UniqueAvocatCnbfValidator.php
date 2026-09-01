<?php

namespace MonIndemnisationJustice\Validation\Validator;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\Avocat;
use MonIndemnisationJustice\Entity\Usager;
use MonIndemnisationJustice\Validation\Constraint\UniqueAvocatCnbf;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class UniqueAvocatCnbfValidator extends ConstraintValidator
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof UniqueAvocatCnbf) {
            throw new UnexpectedTypeException($constraint, UniqueAvocatCnbf::class);
        }

        // NotBlank/Regex gèrent déjà les valeurs vides ou mal formées.
        if (!\is_string($value) || !preg_match('/^\d{6}$/', $value)) {
            return;
        }

        // Pas d'avocat connu sous ce numéro : AvocatConnu remontera déjà la violation, rien à ajouter ici.
        $avocat = $this->em->getRepository(Avocat::class)->find($value);
        if (null === $avocat) {
            return;
        }

        if (null !== $this->em->getRepository(Usager::class)->findOneBy(['avocat' => $avocat])) {
            $this->context->buildViolation($constraint->message)->addViolation();
        }
    }
}
