<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Output;

use MonIndemnisationJustice\Entity\Agent;
use Symfony\Component\ObjectMapper\Attribute\Map;

#[Map(source: Agent::class)]
final class AgentOutput
{
    public int $id;
    public string $nom;
    public string $prenom;
    #[Map(source: 'email')]
    public string $courriel;
    #[Map(source: 'administration.value')]
    public string $administration;
    public array $roles;
    #[Map(source: 'dateCreation')]
    public ?\DateTimeInterface $dateCreation = null;
}
