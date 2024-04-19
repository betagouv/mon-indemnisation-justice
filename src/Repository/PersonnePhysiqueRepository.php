<?php

namespace App\Repository;

use App\Entity\PersonnePhysique;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PersonnePhysique>
 *
 * @method PersonnePhysique|null find($id, $lockMode = null, $lockVersion = null)
 * @method PersonnePhysique|null findOneBy(array $criteria, array $orderBy = null)
 * @method PersonnePhysique[]    findAll()
 * @method PersonnePhysique[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PersonnePhysiqueRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PersonnePhysique::class);
    }

    //    /**
    //     * @return PersonnePhysique[] Returns an array of PersonnePhysique objects
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

    //    public function findOneBySomeField($value): ?PersonnePhysique
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
