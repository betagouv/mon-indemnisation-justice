<?php

namespace MonIndemnisationJustice\Api\Public\Dysfonctionnement\Input;

use MonIndemnisationJustice\Entity\TestEligibiliteDysfonctionnement;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Validator\Constraints as Assert;

class TestEligibiliteDysfonctionnementInput
{
    #[Assert\NotNull(message: 'Veuillez indiquer si la procédure est terminée')]
    public ?bool $procedureTerminee = null;

    #[Context([DateTimeNormalizer::FORMAT_KEY => 'Y-m-d'])]
    #[Assert\NotNull(message: 'La date de la décision est requise')]
    public ?\DateTimeImmutable $dateDecision = null;

    #[Assert\NotNull(message: "Veuillez indiquer s'il existe une action contentieuse en cours")]
    public ?bool $aUneActionContentieuse = null;

    /**
     * @var string[]
     */
    #[Assert\NotNull]
    #[Assert\Count(min: 1, minMessage: 'Veuillez sélectionner au moins un type de décision')]
    #[Assert\All([
        new Assert\Choice(
            choices: ['jugement_premiere_instance', 'arret_cour_appel', 'arret_cour_cassation', 'aucune'],
            message: 'Type de décision invalide'
        ),
    ])]
    public ?array $typesDecision = null;

    /**
     * @var string[]
     */
    #[Assert\NotNull]
    #[Assert\Count(min: 1, minMessage: 'Veuillez sélectionner au moins une pièce de procédure')]
    #[Assert\All([
        new Assert\Choice(
            choices: ['assignation', 'decisions_juge', 'calendrier', 'ecritures', 'convocations', 'renvoi', 'echanges', 'appel'],
            message: 'Pièce de procédure invalide'
        ),
    ])]
    public ?array $piecesProcedure = null;

    #[Assert\NotNull(message: 'Veuillez indiquer si vous disposez de preuves de diligences')]
    public ?bool $preuvesDiligences = null;

    public function versTestEligibiliteDysfonctionnement(?TestEligibiliteDysfonctionnement $source = null): TestEligibiliteDysfonctionnement
    {
        $test = $source ?? new TestEligibiliteDysfonctionnement();
        $test->procedureTerminee = $this->procedureTerminee;
        $test->dateDecision = $this->dateDecision;
        $test->aUneActionContentieuse = $this->aUneActionContentieuse;
        $test->typesDecision = $this->typesDecision;
        $test->piecesProcedure = $this->piecesProcedure;
        $test->preuvesDiligences = $this->preuvesDiligences;

        return $test;
    }
}
