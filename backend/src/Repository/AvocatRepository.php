<?php

namespace MonIndemnisationJustice\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use MonIndemnisationJustice\Entity\Avocat;

/**
 * @extends ServiceEntityRepository<Avocat>
 *
 * @method Avocat|null find($id, $lockMode = null, $lockVersion = null)
 * @method Avocat|null findOneBy(array $criteria, array $orderBy = null)
 * @method Avocat[]    findAll()
 * @method Avocat[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AvocatRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Avocat::class);
    }

    /**
     * Recherche par nom uniquement 
     *
     * @return Avocat[]
     */
    public function rechercher(string $recherche, int $limite = 10): array
    {
        $mots = array_filter(array_map('trim', explode(' ', $recherche)));

        if (empty($mots)) {
            return [];
        }

        $qb = $this->createQueryBuilder('a')
            ->join('a.barreau', 'b')
            ->addSelect('b')
            ->orderBy('a.nom', 'ASC')
            ->addOrderBy('a.prenom', 'ASC')
            ->setMaxResults($limite);

        foreach (array_values($mots) as $index => $mot) {
            $qb
                ->andWhere("LOWER(a.nom) LIKE :mot{$index}")
                ->setParameter("mot{$index}", '%'.strtolower($mot).'%');
        }

        return $qb->getQuery()->getResult();
    }
}
