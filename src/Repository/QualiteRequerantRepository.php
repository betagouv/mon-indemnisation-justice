<?php

namespace App\Repository;

use App\Entity\QualiteRequerant;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<QualiteRequerant>
 *
 * @method QualiteRequerant|null find($id, $lockMode = null, $lockVersion = null)
 * @method QualiteRequerant|null findOneBy(array $criteria, array $orderBy = null)
 * @method QualiteRequerant[]    findAll()
 * @method QualiteRequerant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QualiteRequerantRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, QualiteRequerant::class);
    }

    //    /**
    //     * @return QualiteRequerant[] Returns an array of QualiteRequerant objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('q')
    //            ->andWhere('q.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('q.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?QualiteRequerant
    //    {
    //        return $this->createQueryBuilder('q')
    //            ->andWhere('q.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
