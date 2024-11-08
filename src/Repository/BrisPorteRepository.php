<?php

namespace App\Repository;

use App\Entity\BrisPorte;
use App\Entity\EtatDossier;
use App\Entity\EtatDossierType;
use App\Entity\Requerant;
use App\Service\PasswordGenerator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BrisPorte>
 *
 * @method BrisPorte|null find($id, $lockMode = null, $lockVersion = null)
 * @method BrisPorte|null findOneBy(array $criteria, array $orderBy = null)
 * @method BrisPorte[]    findAll()
 * @method BrisPorte[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BrisPorteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BrisPorte::class);
    }

    public function save(BrisPorte $dossier, bool $flush = true): void
    {
        $this->getEntityManager()->persist($dossier);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Retourne la liste des dossiers constitués (i.e. dont la valeur de `dateDeclaration` est non nulle).
     *
     * @return BrisPorte[]
     */
    public function getDossiersConstitues(): array
    {
        $qb = $this->createQueryBuilder('b');

        return $qb
            ->from(BrisPorte::class, 'bp')
            ->where($qb->expr()->isNotNull('bp.dateDeclaration'))
            ->getQuery()
            ->getResult();
    }

    public function nouveauDossier(Requerant $requerant): BrisPorte
    {
        $dossier = (new BrisPorte())->setRequerant($requerant);

        $dossier->changerStatut(EtatDossierType::DOSSIER_INITIE, requerant: true);

        return $dossier;
    }

    public function generateRaccourci(int $length = 8): string
    {
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT id FROM public.bris_porte WHERE raccourci = :raccourci';
        $req = $conn->prepare($sql);
        do {
            $password = mb_strtoupper(PasswordGenerator::new(length: $length, withSpecialChars: false));
            $req->bindValue('raccourci', $password);
            $stmt = $req->executeQuery();
            $result = $stmt->fetchOne();
        } while ($result);

        return $password;
    }

    public function getForRequerant(Requerant $requerant): array
    {
        return $this->findBy(['requerant' => $requerant]);
    }

    public function findByStatuts(array $statuts = [], array $orderBy = [], int $offset = 0, int $limit = 10): array
    {
        return $this->findAll();
    }
}
