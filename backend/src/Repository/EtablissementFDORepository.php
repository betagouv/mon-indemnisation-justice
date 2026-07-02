<?php

namespace MonIndemnisationJustice\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
}
