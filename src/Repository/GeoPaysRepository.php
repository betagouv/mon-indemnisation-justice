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
            ->where('p.codeInsee is not null')
            ->orderBy('p.code', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getFrance(): GeoPays
    {
        return $this->findOneBy(['codeFrance' => GeoPays::CODE_INSEE_FRANCE]);
    }
}
