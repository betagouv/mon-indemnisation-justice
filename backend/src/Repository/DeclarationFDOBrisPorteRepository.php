<?php

namespace MonIndemnisationJustice\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;

/**
 * @extends DeclarationFDOBrisPorteRepository<DeclarationFDOBrisPorte>
 *
 * @method DeclarationFDOBrisPorte|null find($id, $lockMode = null, $lockVersion = null)
 * @method DeclarationFDOBrisPorte|null findOneBy(array $criteria, array $orderBy = null)
 * @method DeclarationFDOBrisPorte[]    findAll()
 * @method DeclarationFDOBrisPorte[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeclarationFDOBrisPorteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeclarationFDOBrisPorte::class);
    }

    /**
     * Recherche une déclaration pouvant être rattachée à un dossier, donc soumise par l'agent FDO (en théorie une déclaration
     * en brouillon n'a pas encore de référence) et non encore rattachée à un dossier.
     */
    public function findARattacher(string $reference): ?DeclarationFDOBrisPorte
    {
        return $this->createQueryBuilder('d')
            ->where('d.reference = :reference')
            ->setParameter('reference', $reference)
            // Pas encore rattaché à un dossier
            ->andWhere('d.brisPorte is null')
            // Déclaration soumise (plus en brouillon)
            ->andWhere('d.dateSoumission is not null')
            ->getQuery()
            ->getOneOrNullResult();
    }
}
