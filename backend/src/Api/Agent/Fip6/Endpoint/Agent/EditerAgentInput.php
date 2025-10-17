<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Agent;

use MonIndemnisationJustice\Entity\Administration;
use MonIndemnisationJustice\Entity\Agent;
use Symfony\Component\ObjectMapper\Attribute\Map;
use Symfony\Component\Validator\Constraints as Assert;

#[Map(Agent::class)]
class EditerAgentInput
{
    public string $prenom;
    public string $nom;
    #[Assert\Email(message: "L'adresse email n'est pas valide")]
    #[Map(target: 'email')]
    public string $courriel;
    #[Assert\Choice(choices: [Administration::MINISTERE_JUSTICE, Administration::POLICE_NATIONALE, Administration::GENDARMERIE_NATIONALE], message: "Cette administration n'est pas reconnue")]
    public Administration $administration;
    #[Assert\Choice(callback: [Agent::class, 'roles'], multiple: true, message: "Au moins un rôle n'a pas été reconnu")]
    public array $roles = [];
}
