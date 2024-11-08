<?php

namespace App\Entity;

use App\Repository\EtatDossierRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

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

    #[ORM\ManyToOne(targetEntity: BrisPorte::class, inversedBy: 'historiqueEtats')]
    #[ORM\JoinColumn(nullable: false, name: 'dossier_id', referencedColumnName: 'id')]
    protected BrisPorte $dossier;

    #[ORM\ManyToOne(targetEntity: Agent::class)]
    #[ORM\JoinColumn(name: 'agent_id', referencedColumnName: 'id')]
    protected ?Agent $agent;

    #[ORM\ManyToOne(targetEntity: Requerant::class)]
    #[ORM\JoinColumn(name: 'requerant_id', referencedColumnName: 'id')]
    protected ?Requerant $requerant;

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
