<?php

namespace MonIndemnisationJustice\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use MonIndemnisationJustice\Entity\Agent;

/**
 * @extends ServiceEntityRepository<Agent>
 *
 * @method null|Agent find($id, $lockMode = null, $lockVersion = null)
 * @method null|Agent findOneBy(array $criteria, array $orderBy = null)
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
            ->orderBy('a.nom')
        ;

        foreach ($roles as $index => $role) {
            $qb->orWhere("a.roles LIKE :role{$index}");
            $qb->setParameter("role{$index}", "%{$role}%");
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Retourne la liste des agents disposant explicitement du rôle 'ROLE_AGENT_REDACTEUR'.
     *
     * @return Agent[]
     */
    public function getRedacteurs(): array
    {
        return $this->findByRoles([Agent::ROLE_AGENT_REDACTEUR]);
    }

    /**
     * Retourne la liste des agents disposant explicitement du rôle 'ROLE_AGENT_ATTRIBUTEUR'.
     *
     * @return Agent[]
     */
    public function getAttributeurs(): array
    {
        return $this->findByRoles([Agent::ROLE_AGENT_ATTRIBUTEUR]);
    }

    /**
     * Retourne la liste des agents disposant explicitement du rôle 'ROLE_AGENT_VALIDATEUR'.
     *
     * @return Agent[]
     */
    public function getValidateurs(): array
    {
        return $this->findByRoles([Agent::ROLE_AGENT_VALIDATEUR]);
    }

    /**
     * Retourne la liste des agents disposant explicitement du rôle 'ROLE_AGENT_LIAISON_BUDGET'.
     *
     * @return Agent[]
     */
    public function getAgentsLiaisonBudget(): array
    {
        return $this->findByRoles([Agent::ROLE_AGENT_LIAISON_BUDGET]);
    }

    /**
     * Retourne la liste des agents en attente d'être validés.
     *
     * @return Agent[]
     */
    public function getEnAttenteValidation(): array
    {
        return $this
            ->createQueryBuilder('a')
            ->where('a.estValide = false')
            ->orderBy('a.dateCreation', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Retourne la liste des agents validés.
     *
     * @return Agent[]
     */
    public function getActifs(): array
    {
        return $this
            ->createQueryBuilder('a')
            ->where('a.estValide = true')
            ->orderBy('a.dateCreation', 'DESC')
            ->getQuery()
            ->getResult()
        ;
    }
}
