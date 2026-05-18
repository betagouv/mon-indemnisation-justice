<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Agent;

use MonIndemnisationJustice\Api\Requerant\Dossier\Normalization\EntityResolveur;
use MonIndemnisationJustice\Entity\Administration;
use MonIndemnisationJustice\Entity\AdministrationType;
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
    #[Assert\Choice(choices: [AdministrationType::MINISTERE_JUSTICE, AdministrationType::POLICE_NATIONALE, AdministrationType::GENDARMERIE_NATIONALE], message: "Cette administration n'est pas reconnue")]
    public AdministrationType $administration;
    #[Assert\Choice(callback: [Agent::class, 'roles'], multiple: true, message: "Au moins un rôle n'a pas été reconnu")]
    public array $roles = [];

    public function versAgent(): Agent
    {
        return new Agent()
            ->setPrenom($this->prenom)
            ->setNom($this->nom)
            ->setEmail($this->courriel)
            ->setAdministration(EntityResolveur::resoudre(Administration::class, $this->administration))
            ->setRoles($this->roles);
    }
}
