<?php

namespace MonIndemnisationJustice\Validation\Validator;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Public\Dysfonctionnement\Input\InscriptionAvocatInput;
use MonIndemnisationJustice\Entity\Avocat;
use MonIndemnisationJustice\Validation\Constraint\AvocatValide;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class AvocatValideValidator extends ConstraintValidator
{
    public function __construct(
        private readonly EntityManagerInterface $em,
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

        // Numéro ou barreau vide/mal formé : géré par les contraintes NotBlank/Regex de propriété. La présence
        // de l'avocat dans l'annuaire est garantie par AvocatConnu (contrainte de propriété) : si le find()
        // ci-dessous ne retourne rien, cette violation-là sera déjà remontée, inutile d'en ajouter une seconde.
        if ('' === $value->barreauId || !preg_match('/^\d{6}$/', $value->numeroCnbf)) {
            return;
        }

        $avocat = $this->em->getRepository(Avocat::class)->find($value->numeroCnbf);

        if (null !== $avocat && $avocat->getBarreau()->getId() !== $value->barreauId) {
            $this->context->buildViolation($constraint->message)
                ->atPath('barreauId')
                ->addViolation();
        }
    }
}
