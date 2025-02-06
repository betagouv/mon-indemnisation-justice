<?php

namespace MonIndemnisationJustice\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Entity\Requerant;
use MonIndemnisationJustice\Service\PasswordGenerator;

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
     * @param EtatDossierType[] $etats
     * @param Agent[]           $attributaires
     *
     * @return BrisPorte[]
     */
    public function rechercheDossiers(array $etats = [], array $attributaires = [], array $filtres = [], bool $nonAttribue = false)
    {
        $qb = $this
            ->createQueryBuilder('d')
            ->join('d.etatDossier', 'e')
            ->join('d.adresse', 'a')
            ->join('d.requerant', 'r')
            ->join('r.personnePhysique', 'pp')
        ;

        if (!empty($etats)) {
            $qb
                ->andWhere('e.etat in (:etats)')
                ->setParameter('etats', $etats);
        }

        if (!empty($filtres)) {
            $wheres = [];

            foreach ($filtres as $index => $filtre) {
                $wheres[] = "LOWER(a.codePostal) LIKE :filtre$index";
                $wheres[] = "LOWER(a.ligne1) LIKE :filtre$index";
                $wheres[] = "LOWER(a.localite) LIKE :filtre$index";
                $wheres[] = "LOWER(pp.nom) LIKE :filtre$index";
                $wheres[] = "LOWER(pp.prenom1) LIKE :filtre$index";
                $qb->setParameter("filtre$index", strtolower("%$filtre%"));
            }
            $qb->andWhere($qb->expr()->orX(...$wheres));
        }

        if (!empty($attributaires)) {
            $qb
                ->andWhere(
                    'd.redacteur in (:redacteurs)'.($nonAttribue ? ' or d.redacteur is null' : '')
                )
                ->setParameter('redacteurs', array_map(fn ($a) => $a->getId(), $attributaires));
        } elseif ($nonAttribue) {
            $qb->andWhere('d.redacteur is null');
        }

        return $qb->getQuery()->getResult();
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
