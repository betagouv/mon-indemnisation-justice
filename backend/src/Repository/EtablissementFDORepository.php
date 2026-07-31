<?php

namespace MonIndemnisationJustice\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;
use MonIndemnisationJustice\Entity\Administration;
use MonIndemnisationJustice\Entity\FDO\EtablissementFDO;
use MonIndemnisationJustice\Entity\GeoCodePostal;

/**
 * @extends ServiceEntityRepository<EtablissementFDO>
 *
 * @method EtablissementFDO|null find($id, $lockMode = null, $lockVersion = null)
 * @method EtablissementFDO|null findOneBy(array $criteria, array $orderBy = null)
 * @method EtablissementFDO[]    findAll()
 * @method EtablissementFDO[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EtablissementFDORepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EtablissementFDO::class);
    }

    public function getByNom(string $nom): ?EtablissementFDO
    {
        return $this->findOneBy(['nom' => $nom]);
    }

    public function getOrCreate(Administration $administration, GeoCodePostal $codePostal): ?EtablissementFDO
    {
        return $this->findOneBy(
            [
                'administration' => $administration,
                'codePostal' => $codePostal,
            ]
        ) ?? new EtablissementFDO()->setAdministration($administration)
            ->setCodePostal($codePostal);
    }

    /**
     * @return array<EtablissementFDO>
     */
    public function rechercher(Administration $administration, string $recherche, int $limit = 10): array
    {
        $qb = $this->createQueryBuilder('e');
        $mots = array_filter(
            array_map(
                fn (string $mot) => trim($mot),
                explode(' ', $recherche)
            ),
            fn (string $mot) => !empty($mot) && !in_array(strtolower($mot), ['commissariat', 'gendarmerie'])
        );

        return $qb
            ->join('e.codePostal', 'cp')
            ->where('e.administration = :administration')
            ->andWhere(
                $qb->expr()->orX(
                    'cp.codePostal = :codePostal',
                    ...array_map(
                        fn (string $mot, int $index) => "LOWER(e.nom) LIKE :mot$index",
                        $mots,
                        array_keys($mots)
                    )
                )
            )
            ->setParameters(
                new ArrayCollection(
                    [
                        new Parameter('administration', $administration),
                        new Parameter('codePostal', $recherche),
                        ...array_map(
                            fn (string $mot, int $index) => new Parameter("mot$index", '%'.strtolower($mot).'%'),
                            $mots,
                            array_keys($mots)
                        ),
                    ]
                )
            )
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }
}
