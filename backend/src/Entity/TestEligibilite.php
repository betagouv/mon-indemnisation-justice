<?php

namespace MonIndemnisationJustice\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity]
#[ORM\Table(name: 'eligibilite_tests')]
#[ORM\HasLifecycleCallbacks]
class TestEligibilite
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    public ?int $id = null;

    #[ORM\ManyToOne(targetEntity: GeoDepartement::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'departement_code', referencedColumnName: 'code', nullable: true, onDelete: 'SET NULL')]
    public ?GeoDepartement $departement;

    #[Groups(['agent:detail', 'requerant:detail'])]
    #[ORM\Column(nullable: true)]
    public bool $estVise = false;

    #[Groups(['agent:detail', 'requerant:detail'])]
    #[ORM\Column(nullable: true, options: ['comments' => 'La personne recherchée réside ou est hébergée à cette adresse'])]
    public ?bool $estHebergeant = null;

    #[Groups(['agent:detail', 'requerant:detail'])]
    #[ORM\Column(type: 'string', length: 3, nullable: true, enumType: QualiteRequerant::class)]
    public ?QualiteRequerant $rapportAuLogement = null;

    #[Groups(['agent:detail', 'requerant:detail'])]
    #[ORM\Column(nullable: true)]
    public ?bool $aContacteAssurance = null;

    #[Groups(['agent:detail', 'requerant:detail'])]
    #[ORM\Column(nullable: true)]
    public ?bool $aContacteBailleur = null;

    #[ORM\ManyToOne(targetEntity: Requerant::class, cascade: ['persist'])]
    #[ORM\JoinColumn(unique: false, nullable: true, onDelete: 'CASCADE')]
    public ?Requerant $requerant = null;

    #[ORM\Column]
    public bool $estEligibleExperimentation = false;

    #[ORM\Column(options: ['default' => true])]
    public bool $estIssuAttestation = false;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    public ?\DateTimeInterface $dateSoumission = null;

    #[ORM\PrePersist]
    public function prePersist()
    {
        if (null === $this->dateSoumission) {
            $this->dateSoumission = new \DateTime();
        }
    }

    public function estEligible(): bool
    {
        return !$this->estVise && !$this->estHebergeant;
    }

    public static function fromArray(array $values): TestEligibilite
    {
        $testEligibilite = new TestEligibilite();

        $testEligibilite->departement = $values['departement'] ?? null;
        $testEligibilite->estEligibleExperimentation = $testEligibilite->departement?->estDeploye() ?? false;
        $testEligibilite->estVise = $values['estVise'] ?? false;
        $testEligibilite->estHebergeant = $values['estHebergeant'] ?? null;
        $testEligibilite->rapportAuLogement = $values['rapportAuLogement'] ?? null;
        $testEligibilite->aContacteAssurance = $values['aContacteAssurance'] ?? null;
        $testEligibilite->aContacteBailleur = $values['aContacteBailleur'] ?? null;
        $testEligibilite->requerant = $values['requerant'] ?? null;
        $testEligibilite->dateSoumission = $values['dateSoumission'] ?? null;

        return $testEligibilite;
    }
}
