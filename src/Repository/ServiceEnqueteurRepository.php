<?php

namespace App\Repository;

use App\Entity\ServiceEnqueteur;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ServiceEnqueteur>
 *
 * @method ServiceEnqueteur|null find($id, $lockMode = null, $lockVersion = null)
 * @method ServiceEnqueteur|null findOneBy(array $criteria, array $orderBy = null)
 * @method ServiceEnqueteur[]    findAll()
 * @method ServiceEnqueteur[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ServiceEnqueteurRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ServiceEnqueteur::class);
    }

    //    /**
    //     * @return ServiceEnqueteur[] Returns an array of ServiceEnqueteur objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ServiceEnqueteur
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
