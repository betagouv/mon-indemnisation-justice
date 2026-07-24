<?php

namespace MonIndemnisationJustice\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use MonIndemnisationJustice\Entity\FDO\EtablissementFDO;

#[ORM\Entity]
#[ORM\Table(name: 'fdo_affectations')]
class AffectationAgentFDO
{
    #[ORM\Id, ORM\ManyToOne(targetEntity: Agent::class, inversedBy: 'affectations'), ORM\JoinColumn('agent_id')]
    protected Agent $agent;
    #[ORM\Id, ORM\ManyToOne(targetEntity: Agent::class, inversedBy: 'affectations'), ORM\JoinColumn('etablissement_id')]
    protected EtablissementFDO $etablissement;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    protected \DateTimeImmutable $dateAffectation;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    protected ?\DateTimeImmutable $dateMutation = null;

    public function getAgent(): Agent
    {
        return $this->agent;
    }

    public function setAgent(Agent $agent): AffectationAgentFDO
    {
        $this->agent = $agent;

        return $this;
    }

    public function getEtablissement(): EtablissementFDO
    {
        return $this->etablissement;
    }

    public function setEtablissement(EtablissementFDO $etablissement): AffectationAgentFDO
    {
        $this->etablissement = $etablissement;

        return $this;
    }

    public function getDateAffectation(): \DateTimeImmutable
    {
        return $this->dateAffectation;
    }

    public function setDateAffectation(\DateTimeImmutable $dateAffectation): AffectationAgentFDO
    {
        $this->dateAffectation = $dateAffectation;

        return $this;
    }

    public function getDateMutation(): ?\DateTimeImmutable
    {
        return $this->dateMutation;
    }

    public function setDateMutation(?\DateTimeImmutable $dateMutation): AffectationAgentFDO
    {
        $this->dateMutation = $dateMutation;

        return $this;
    }

    public function estActive(): ?bool
    {
        return null === $this->dateMutation;
    }
}
