<?php

namespace MonIndemnisationJustice\Repository;

use MonIndemnisationJustice\Entity\PersonneMorale;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PersonneMorale>
 *
 * @method PersonneMorale|null find($id, $lockMode = null, $lockVersion = null)
 * @method PersonneMorale|null findOneBy(array $criteria, array $orderBy = null)
 * @method PersonneMorale[]    findAll()
 * @method PersonneMorale[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonneMoraleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PersonneMorale::class);
    }

    //    /**
    //     * @return PersonneMorale[] Returns an array of PersonneMorale objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?PersonneMorale
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
