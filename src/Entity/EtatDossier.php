<?php

namespace MonIndemnisationJustice\Entity;

use ApiPlatform\Metadata\ApiResource;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use MonIndemnisationJustice\Repository\EtatDossierRepository;
use MonIndemnisationJustice\Service\DateConvertisseur;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Serializer\Attribute\SerializedName;

#[ApiResource(
    operations: [],
)]
#[ORM\Entity(repositoryClass: EtatDossierRepository::class)]
#[ORM\Table(name: 'dossier_etats')]
class EtatDossier
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    protected ?int $id = null;

    #[Groups(['agent:liste', 'agent:detail', 'requerant:detail'])]
    #[ORM\Column(type: 'string', nullable: false, enumType: EtatDossierType::class)]
    protected EtatDossierType $etat;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, name: 'date')]
    protected \DateTimeInterface $dateEntree;

    #[ORM\ManyToOne(targetEntity: BrisPorte::class, inversedBy: 'historiqueEtats', cascade: ['detach'])]
    #[ORM\JoinColumn(nullable: false, name: 'dossier_id', referencedColumnName: 'id')]
    protected ?BrisPorte $dossier;

    #[ORM\ManyToOne(targetEntity: Agent::class, cascade: ['detach'])]
    #[ORM\JoinColumn(name: 'agent_id', referencedColumnName: 'id', onDelete: 'SET NULL')]
    protected ?Agent $agent;

    #[ORM\ManyToOne(targetEntity: Requerant::class, cascade: ['detach'])]
    #[ORM\JoinColumn(name: 'requerant_id', referencedColumnName: 'id', )]
    protected ?Requerant $requerant;

    #[Groups(['agent:liste', 'agent:detail', 'requerant:detail'])]
    #[ORM\Column(type: 'json', nullable: true)]
    protected ?array $contexte;

    #[Groups(['agent:liste', 'agent:detail', 'requerant:detail'])]
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEtat(): EtatDossierType
    {
        return $this->etat;
    }

    public function estASigner(): bool
    {
        return $this->etat->estASigner();
    }

    public function estDecide(): bool
    {
        return $this->etat->estDecide();
    }

    public function estSigne(): bool
    {
        return $this->etat->estSigne();
    }

    public function estAccepte(): bool
    {
        return $this->etat->estAccepte();
    }

    public function estRejete(): bool
    {
        return $this->etat->estRejete();
    }

    public function aValider(): bool
    {
        return $this->estASigner();
    }

    public function getLibelle(): string
    {
        return $this->etat->getLibelle();
    }

    #[Groups(['agent:liste', 'agent:detail', 'requerant:detail'])]
    #[SerializedName('dateEntree')]
    public function getDateEntreeTimestamp(): ?int
    {
        return DateConvertisseur::enMillisecondes($this->dateEntree);
    }

    public function getDate(): \DateTimeInterface
    {
        return $this->dateEntree;
    }

    public function getDossier(): BrisPorte
    {
        return $this->dossier;
    }

    public function getAgent(): ?Agent
    {
        return $this->agent;
    }

    #[Groups(['agent:detail', 'agent:liste', 'requerant:detail'])]
    #[SerializedName('redacteur')]
    public function getReferenceAgent(): ?int
    {
        return $this->agent?->getId();
    }

    #[Groups(['agent:detail'])]
    public function getRequerant(): ?Requerant
    {
        return $this->requerant;
    }

    #[Groups(['agent:liste'])]
    #[SerializedName('requerant')]
    public function getEstRequerant(): bool
    {
        return null !== $this->requerant;
    }

    public function getContexte(): array
    {
        return $this->contexte;
    }

    public function addContexte(array $contexte): array
    {
        return $this->contexte = array_merge_recursive($this->contexte, $contexte);
    }

    final public static function creer(BrisPorte $dossier, EtatDossierType $etat, ?array $contexte = null): static
    {
        $nouvelEtat = (new self());
        $nouvelEtat->dossier = $dossier;
        $nouvelEtat->etat = $etat;
        $nouvelEtat->dateEntree = new \DateTimeImmutable();
        $nouvelEtat->contexte = $contexte;

        return $nouvelEtat;
    }

    public static function creerRequerant(BrisPorte $dossier, EtatDossierType $etat, ?array $contexte = null): static
    {
        $nouvelEtat = self::creer($dossier, $etat, $contexte);
        $nouvelEtat->requerant = $dossier->getRequerant();

        return $nouvelEtat;
    }

    public static function creerAgent(BrisPorte $dossier, EtatDossierType $etat, Agent $agent, ?array $contexte = null): static
    {
        $nouvelEtat = self::creer($dossier, $etat, $contexte);
        $nouvelEtat->agent = $agent;

        return $nouvelEtat;
    }
}
