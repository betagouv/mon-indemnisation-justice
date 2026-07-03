<?php

namespace MonIndemnisationJustice\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use MonIndemnisationJustice\Entity\GeoCommune;
use MonIndemnisationJustice\Entity\GeoDepartement;

/**
 * @extends ServiceEntityRepository<GeoCommune>
 *
 * @method GeoCommune|null find($id, $lockMode = null, $lockVersion = null)
 * @method GeoCommune|null findOneBy(array $criteria, array $orderBy = null)
 * @method GeoCommune[]    findAll()
 * @method GeoCommune[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GeoCommuneRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GeoCommune::class);
    }

    public function getOrCreate(string $code, string $nom, ?GeoDepartement $departement = null): GeoCommune
    {
        return $this->find($code) ?? new GeoCommune()->setCode($code)->setNom($nom)->setDepartement($departement);
    }
}
