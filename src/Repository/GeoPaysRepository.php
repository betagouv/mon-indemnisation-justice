<?php

namespace App\Repository;

use App\Entity\GeoPays;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;


/**
 * @extends ServiceEntityRepository<GeoPays>
 *
 * @method GeoPays|null find($id, $lockMode = null, $lockVersion = null)
 * @method GeoPays|null findOneBy(array $criteria, array $orderBy = null)
 * @method GeoPays[]    findAll()
 * @method GeoPays[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GeoPaysRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GeoPays::class);
    }

    /**
     * @return GeoPays[]
     */
    public function getListeTriee(): array
    {
        return $this
            ->createQueryBuilder('p')
            ->orderBy('p.estFrance', 'DESC')
            ->addOrderBy('p.code', 'ASC')
            ->getQuery()
            ->getResult();
    }

}