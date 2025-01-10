<?php

namespace MonIndemnisationJustice\Repository;

use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\BrisPorte;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Agent>
 *
 * @method Agent|null find($id, $lockMode = null, $lockVersion = null)
 * @method Agent|null findOneBy(array $criteria, array $orderBy = null)
 * @method Agent[]    findAll()
 * @method Agent[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AgentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Agent::class);
    }

    public function save(Agent $agent, bool $flush = true): void
    {
        $this->getEntityManager()->persist($agent);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findByRoles(array $roles): array
    {
        $qb = $this
            ->createQueryBuilder('a')
            ->orderBy('a.nom');

        foreach ($roles as $index => $role) {
            $qb->orWhere("a.roles LIKE :role$index");
            $qb->setParameter("role$index", "%$role%");
        }

        return $qb->getQuery()->getResult();
    }
}
