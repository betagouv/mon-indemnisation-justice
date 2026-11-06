<?php

namespace MonIndemnisationJustice\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use MonIndemnisationJustice\Entity\EnvoiCourriel;

/**
 * @extends ServiceEntityRepository<EnvoiCourriel>
 *
 * @method EnvoiCourriel|null find($id, $lockMode = null, $lockVersion = null)
 * @method EnvoiCourriel|null findOneBy(array $criteria, array $orderBy = null)
 * @method EnvoiCourriel[]    findAll()
 * @method EnvoiCourriel[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EnvoiCourrielRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EnvoiCourriel::class);
    }

    public function save(EnvoiCourriel $EnvoiCourriel, bool $flush = true): void
    {
        $this->getEntityManager()->persist($EnvoiCourriel);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
