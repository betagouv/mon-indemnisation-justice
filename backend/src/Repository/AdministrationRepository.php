<?php

namespace MonIndemnisationJustice\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use MonIndemnisationJustice\Entity\Administration;

/**
 * @extends AdministrationRepository<Administration>
 *
 * @method Administration|null find($id, $lockMode = null, $lockVersion = null)
 * @method Administration|null findOneBy(array $criteria, array $orderBy = null)
 * @method Administration[]    findAll()
 * @method Administration[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdministrationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Administration::class);
    }

    public function findBySiret(string $siret): ?Administration
    {
        return $this->findOneBy(['siret' => $siret]);
    }
}
