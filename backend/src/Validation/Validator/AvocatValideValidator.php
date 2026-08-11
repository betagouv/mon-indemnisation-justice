<?php

namespace MonIndemnisationJustice\Validation\Validator;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Public\Dysfonctionnement\Input\InscriptionAvocatInput;
use MonIndemnisationJustice\Entity\Avocat;
use MonIndemnisationJustice\Repository\UsagerRepository;
use MonIndemnisationJustice\Validation\Constraint\AvocatValide;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AvocatValideValidator extends ConstraintValidator
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UsagerRepository $usagerRepository,
    ) {
    }

    public function validate(mixed $value, Constraint $constraint): void
    {
        if (!$constraint instanceof AvocatValide) {
            throw new UnexpectedTypeException($constraint, AvocatValide::class);
        }

        if (!$value instanceof InscriptionAvocatInput) {
            return;
        }

        // Les contraintes NotBlank/Regex sur numeroCnbf/barreauId gèrent déjà les valeurs vides ou mal formées.
        if ('' === $value->numeroCnbf || !preg_match('/^\d{6}$/', $value->numeroCnbf)) {
            return;
        }

        $avocat = $this->em->getRepository(Avocat::class)->find($value->numeroCnbf);

        if (null === $avocat) {
            $this->context->buildViolation($constraint->messageInconnu)
                ->atPath('numeroCnbf')
                ->addViolation();

            return;
        }

        if (null !== $this->usagerRepository->findOneBy(['avocat' => $avocat])) {
            $this->context->buildViolation($constraint->messageDejaInscrit)
                ->atPath('numeroCnbf')
                ->addViolation();

            return;
        }

        if ('' !== $value->barreauId && $avocat->getBarreau()->getId() !== $value->barreauId) {
            $this->context->buildViolation($constraint->messageBarreauIncorrect)
                ->atPath('barreauId')
                ->addViolation();
        }
    }
}
