<?php

namespace MonIndemnisationJustice\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use MonIndemnisationJustice\Repository\EtatDossierRepository;

#[ORM\Entity(repositoryClass: EtatDossierRepository::class)]
#[ORM\Table(name: 'dossier_etats')]
class EtatDossier
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column]
    protected ?int $id = null;

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

    #[ORM\Column(type: 'json', nullable: true)]
    protected ?array $contexte;

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

    public function getRequerant(): ?Requerant
    {
        return $this->requerant;
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
