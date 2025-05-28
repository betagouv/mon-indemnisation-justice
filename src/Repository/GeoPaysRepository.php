<?php

namespace MonIndemnisationJustice\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use MonIndemnisationJustice\Entity\GeoPays;

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
            // On retire les pays sans code INSEE, hormis la France
            ->where("p.codeInsee is not null or p.code = 'FRA'")
            // On souhaite placer la France en premier dans la liste
            ->orderBy("p.code = 'FRA'", 'DESC')
            ->addOrderBy('p.code', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
