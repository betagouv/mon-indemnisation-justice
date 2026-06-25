<?php

namespace MonIndemnisationJustice\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'dysfonctionnement_test_eligibilite')]
#[ORM\HasLifecycleCallbacks]
class TestEligibiliteDysfonctionnement
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\Column(nullable: true)]
    public ?bool $procedureTerminee = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    public ?\DateTimeImmutable $dateDecision = null;

    #[ORM\Column(nullable: true, options: ['comments' => "Indique si une action contentieuse est en cours (bloque l'éligibilité)"])]
    public ?bool $aUneActionContentieuse = null;

    /** @var string[]|null */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    public ?array $typesDecision = null;

    /** @var string[]|null */
    #[ORM\Column(type: Types::JSON, nullable: true)]
    public ?array $piecesProcedure = null;

    #[ORM\Column(nullable: true)]
    public ?bool $preuvesDiligences = null;

    #[ORM\ManyToOne(targetEntity: Usager::class, cascade: ['persist'])]
    #[ORM\JoinColumn(unique: false, nullable: true, onDelete: 'CASCADE')]
    public ?Usager $usager = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    public ?\DateTimeImmutable $dateSoumission = null;

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        if (null === $this->dateSoumission) {
            $this->dateSoumission = new \DateTimeImmutable();
        }
    }

    public function estEligible(): bool
    {
        return $this->estDansLesDelais()
            && $this->aUneActionContentieuse === false
            && !empty($this->typesDecision)
            && !in_array('aucune', $this->typesDecision ?? [])
            && !empty($this->piecesProcedure)
            && !in_array('aucune', $this->piecesProcedure ?? [])
            && $this->preuvesDiligences === true;
    }

    public function getDateExpiration(): ?\DateTimeImmutable
    {
        if (null === $this->dateDecision) {
            return null;
        }

        return (clone $this->dateDecision)
            ->add(new \DateInterval('P5Y'))
            ->modify('first day of january');
    }

    private function estDansLesDelais(): bool
    {
        $expiration = $this->getDateExpiration();

        return null !== $expiration && new \DateTimeImmutable() < $expiration;
    }

    public static function fromArray(array $values): self
    {
        $test = new self();
        $test->procedureTerminee = $values['procedureTerminee'] ?? null;
        $test->dateDecision = $values['dateDecision'] ?? null;
        $test->aUneActionContentieuse = $values['aUneActionContentieuse'] ?? null;
        $test->typesDecision = $values['typesDecision'] ?? null;
        $test->piecesProcedure = $values['piecesProcedure'] ?? null;
        $test->preuvesDiligences = $values['preuvesDiligences'] ?? null;
        $test->usager = $values['usager'] ?? null;
        $test->dateSoumission = $values['dateSoumission'] ?? null;

        return $test;
    }
}
