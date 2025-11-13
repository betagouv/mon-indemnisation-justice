<?php

namespace MonIndemnisationJustice\Api\Agent\FDO\Output;

use MonIndemnisationJustice\Entity\Agent;
use Symfony\Component\ObjectMapper\Attribute\Map;

#[Map(source: Agent::class)]
class AgentOutput
{
    public int $id;
    public string $prenom;
    public string $nom;
    #[Map(source: 'email')]
    public string $courriel;
    public string $telephone;
    public array $roles = [];
    #[Map(source: 'administration.value')]
    public string $administration;
}
