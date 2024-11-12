<?php

namespace App\Entity;

use App\Repository\EtatDossierRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Persistence\Event\LifecycleEventArgs;

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


    #[ORM\ManyToOne(targetEntity: Agent::class)]
    #[ORM\JoinColumn(name: 'agent_id', referencedColumnName: 'id')]
    protected ?Agent $agent;

    #[ORM\ManyToOne(targetEntity: Requerant::class, cascade: ['detach'])]
    #[ORM\JoinColumn(name: 'requerant_id', referencedColumnName: 'id')]
    protected ?Requerant $requerant;

    public function getEtat(): EtatDossierType
    {
        return $this->etat;
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

    final public static function creer(BrisPorte $dossier, EtatDossierType $etat): static
    {
        $nouvelEtat = (new self());
        $nouvelEtat->dossier = $dossier;
        $nouvelEtat->etat = $etat;
        $nouvelEtat->dateEntree = new \DateTimeImmutable();

        return $nouvelEtat;
    }

    public static function creerRequerant(BrisPorte $dossier, EtatDossierType $etat): static
    {
        $nouvelEtat = self::creer($dossier, $etat);
        $nouvelEtat->requerant = $dossier->getRequerant();

        return $nouvelEtat;
    }

    public static function creerAgent(BrisPorte $dossier, EtatDossierType $etat, Agent $agent): static
    {
        $nouvelEtat = self::creer($dossier, $etat);
        $nouvelEtat->agent = $agent;

        return $nouvelEtat;
    }
}
