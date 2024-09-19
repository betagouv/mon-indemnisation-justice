<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class AgentRepository extends EntityRepository
{
    public function getAllActiveAgents(): array
    {
        return $this->findBy(["estActif" => true]);
    }
}