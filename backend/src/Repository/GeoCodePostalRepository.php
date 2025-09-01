<?php

namespace MonIndemnisationJustice\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use MonIndemnisationJustice\Entity\GeoCodePostal;

/**
 * @extends ServiceEntityRepository<GeoCodePostal>
 *
 * @method GeoCodePostal|null find($id, $lockMode = null, $lockVersion = null)
 * @method GeoCodePostal|null findOneBy(array $criteria, array $orderBy = null)
 * @method GeoCodePostal[]    findAll()
 * @method GeoCodePostal[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GeoCodePostalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GeoCodePostal::class);
    }

    /**
     * À partir d'un code INSEE donné, retourne le GeoCodePostal associé en choisissant celui avec la plus petite valeur
     * de code postal.
     *
     * En effet, dans les cas des arrondissements, on optera pour le code postal générique (ex: Marseille sera associée
     * à '13000').
     */
    public function identifier(string $codeInsee): ?GeoCodePostal
    {
        return $this
            ->createQueryBuilder('gcp')
                ->join('gcp.commune', 'gc')
            ->where('gc.code = :codeInsee')
            ->setParameter('codeInsee', $codeInsee)
            ->orderBy('gcp.codePostal', 'asc')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
