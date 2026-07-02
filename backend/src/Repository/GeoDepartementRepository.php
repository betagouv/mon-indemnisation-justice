<?php

namespace MonIndemnisationJustice\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use MonIndemnisationJustice\Entity\GeoDepartement;

/**
 * @extends ServiceEntityRepository<GeoDepartement>
 *
 * @method GeoDepartement|null find($id, $lockMode = null, $lockVersion = null)
 * @method GeoDepartement|null findOneBy(array $criteria, array $orderBy = null)
 * @method GeoDepartement[]    findAll()
 * @method GeoDepartement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GeoDepartementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GeoDepartement::class);
    }

    /**
     * @return GeoDepartement[]
     */
    public function getListeTriee(): array
    {
        return $this
            ->createQueryBuilder('d')
            ->orderBy('d.code', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function getOrCreate(string $codeOuCodePostal, string $nom): ?GeoDepartement
    {
        $code = 5 === strlen($codeOuCodePostal) ? GeoDepartement::extraireCodeDepuisCodePostal($codeOuCodePostal) : $codeOuCodePostal;

        return $this->find($code) ?? new GeoDepartement()->setCode($code)->setNom($nom);
    }
}
