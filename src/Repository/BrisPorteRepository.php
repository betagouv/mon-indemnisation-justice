<?php

namespace MonIndemnisationJustice\Repository;

use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Entity\Requerant;
use MonIndemnisationJustice\Service\PasswordGenerator;
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
     * Retourne la liste des dossiers constitués (i.e. dont l'état actuel est `DOSSIER_DEPOSE`).
     *
     * @return BrisPorte[]
     */
    public function getDossiersConstitues(): array
    {
        return $this->getDossiersParEtat(EtatDossierType::DOSSIER_DEPOSE);
    }

    /**
     * Retourne la liste des dossiers à valider par la personne chef•fe de service (i.e. dont l'état actuel est `DOSSIER_PRE_VALIDE` ou `DOSSIER_PRE_REFUSE`).
     *
     * @return BrisPorte[]
     */
    public function getDossiersAValider(): array
    {
        return $this->getDossiersParEtat(EtatDossierType::DOSSIER_PRE_VALIDE, EtatDossierType::DOSSIER_PRE_REFUSE);
    }

    /**
     * @param EtatDossierType[] $etats
     *
     * @return BrisPorte[]
     */
    protected function getDossiersParEtat(...$etats): array
    {
        return $this->createQueryBuilder('b')
            ->join('b.etatDossier', 'e')
            ->where('e.etat in (:etats)')
            ->setParameter('etats', $etats)
            ->getQuery()
            ->getResult();
    }

    public function decompteParEtat(): array
    {
        return array_merge(
            ...array_map(
                fn (array $row) => [
                    $row['etat']->value => $row['nbDossiers'],
                ],
                $this->createQueryBuilder('b')
                ->join('b.etatDossier', 'e')
                ->select('e.etat', 'COUNT(b.id) AS nbDossiers')
                ->groupBy('e.etat')
                ->getQuery()
                ->getArrayResult()
            )
        );
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
