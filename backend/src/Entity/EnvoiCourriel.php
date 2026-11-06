<?php

namespace MonIndemnisationJustice\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Event\PreFlushEventArgs;
use Doctrine\ORM\Mapping as ORM;
use MonIndemnisationJustice\Repository\EnvoiCourrielRepository;
use Symfony\Component\Uid\Uuid;

/**
 * Décrit l'envoi d'un courriel.
 */
#[ORM\Table(name: 'courriels')]
#[ORM\Entity(repositoryClass: EnvoiCourrielRepository::class)]
#[ORM\HasLifecycleCallbacks]
class EnvoiCourriel
{
    #[ORM\Id]
    #[ORM\Column(type: 'uuid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    protected Uuid $id;

    #[ORM\Column(type: 'string', nullable: false, enumType: EnvoiCourrielType::class)]
    protected EnvoiCourrielType $type;

    #[ORM\Column]
    protected string $recepteur;

    #[ORM\OneToMany(targetEntity: EnvoiCourrielEvenement::class, mappedBy: 'courriel', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['date' => \SortDirection::Ascending])]
    /** @var Collection<EnvoiCourrielEvenement> */
    protected Collection $evenements;

    #[ORM\ManyToOne(targetEntity: DeclarationFDOBrisPorte::class)]
    #[ORM\JoinColumn(onDelete: 'CASCADE')]
    protected ?DeclarationFDOBrisPorte $declaration = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    protected \DateTimeImmutable $dateCreation;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    protected ?\DateTimeImmutable $dateExpedition = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    protected ?\DateTimeImmutable $dateReception = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    protected ?\DateTimeImmutable $dateRejet = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    protected ?\DateTimeImmutable $dateOuverture = null;
    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    protected ?\DateTimeImmutable $dateClic = null;

    public function __construct()
    {
        $this->dateCreation = new \DateTimeImmutable();
        $this->evenements = new ArrayCollection();
    }

    #[ORM\PreFlush]
    public function preFlush(PreFlushEventArgs $args)
    {
        $this->recalculerDates();
    }

    protected function recalculerDates(): void
    {
        $this->dateExpedition = $this->dateEvenement(EnvoiCourrielEvenementType::EXPEDIE);
        $this->dateReception = $this->dateEvenement(EnvoiCourrielEvenementType::RECU);
        $this->dateRejet = $this->dateEvenement(EnvoiCourrielEvenementType::REJETE);
        $this->dateOuverture = $this->dateEvenement(EnvoiCourrielEvenementType::OUVERT);
        $this->dateClic = $this->dateEvenement(EnvoiCourrielEvenementType::CLIQUE);
    }

    /**
     * Calcule la date du premier évènement de type `$typeEvenement` dans la chronologie de l'envoi de courriel.
     */
    protected function dateEvenement(EnvoiCourrielEvenementType $typeEvenement): ?\DateTimeImmutable
    {
        return $this->evenements
            ->filter(
                fn (EnvoiCourrielEvenement $evenement) => $typeEvenement === $evenement->getType()
            )->reduce(
                fn (
                    ?\DateTimeImmutable $datePremierEvenement,
                    EnvoiCourrielEvenement $evenement,
                ) => null === $datePremierEvenement || $evenement->getDate() < $datePremierEvenement ? $evenement->getDate() : $datePremierEvenement,
            );
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getType(): EnvoiCourrielType
    {
        return $this->type;
    }

    public function setType(EnvoiCourrielType $type): EnvoiCourriel
    {
        $this->type = $type;

        return $this;
    }

    public function getRecepteur(): string
    {
        return $this->recepteur;
    }

    public function setRecepteur(string $recepteur): EnvoiCourriel
    {
        $this->recepteur = $recepteur;

        return $this;
    }

    public function getDeclaration(): ?DeclarationFDOBrisPorte
    {
        return $this->declaration;
    }

    public function setDeclaration(?DeclarationFDOBrisPorte $declaration): EnvoiCourriel
    {
        $this->declaration = $declaration;

        return $this;
    }

    public function ajouterEvenement(EnvoiCourrielEvenement $evenement): self
    {
        // Si pas doublon
        if (!$this->evenements->exists(fn (int|string $cle, EnvoiCourrielEvenement $e) => $e->equivautA($evenement))) {
            $this->evenements->add($evenement);

            // On recalcule la date impactée
            switch ($evenement->getType()) {
                case EnvoiCourrielEvenementType::EXPEDIE:
                    $this->dateExpedition = $this->dateEvenement(EnvoiCourrielEvenementType::EXPEDIE);
                    break;
                case EnvoiCourrielEvenementType::REJETE:
                    $this->dateRejet = $this->dateEvenement(EnvoiCourrielEvenementType::REJETE);
                    break;
                case EnvoiCourrielEvenementType::RECU:
                    $this->dateReception = $this->dateEvenement(EnvoiCourrielEvenementType::RECU);
                    break;
                case EnvoiCourrielEvenementType::OUVERT:
                    $this->dateOuverture = $this->dateEvenement(EnvoiCourrielEvenementType::OUVERT);
                    break;
                case EnvoiCourrielEvenementType::CLIQUE:
                    $this->dateClic = $this->dateEvenement(EnvoiCourrielEvenementType::CLIQUE);
                    break;
            }
        }

        return $this;
    }
}
