<?php

namespace App\Repository;

use App\Entity\LiasseDocumentaire;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LiasseDocumentaire>
 *
 * @method LiasseDocumentaire|null find($id, $lockMode = null, $lockVersion = null)
 * @method LiasseDocumentaire|null findOneBy(array $criteria, array $orderBy = null)
 * @method LiasseDocumentaire[]    findAll()
 * @method LiasseDocumentaire[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LiasseDocumentaireRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LiasseDocumentaire::class);
    }

    //    /**
    //     * @return LiasseDocumentaire[] Returns an array of LiasseDocumentaire objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('l.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?LiasseDocumentaire
    //    {
    //        return $this->createQueryBuilder('l')
    //            ->andWhere('l.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
