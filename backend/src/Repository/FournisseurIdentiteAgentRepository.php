<?php

namespace MonIndemnisationJustice\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use MonIndemnisationJustice\Entity\FournisseurIdentiteAgent;

/**
 * @extends ServiceEntityRepository<FournisseurIdentiteAgent>
 *
 * @method FournisseurIdentiteAgent|null find($id, $lockMode = null, $lockVersion = null)
 * @method FournisseurIdentiteAgent|null findOneBy(array $criteria, array $orderBy = null)
 * @method FournisseurIdentiteAgent[]    findAll()
 * @method FournisseurIdentiteAgent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class FournisseurIdentiteAgentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FournisseurIdentiteAgent::class);
    }
}
