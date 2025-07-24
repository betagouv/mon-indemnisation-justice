<?php

namespace MonIndemnisationJustice\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\EtatDossierType;

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
    public function rechercheDossiers(array $etats = [], array $attributaires = [], array $filtres = [], bool $nonAttribue = false): array
    {
        $qb = $this
            ->createQueryBuilder('d')
            ->join('d.etatDossier', 'e')
            ->join('d.adresse', 'a')
            ->join('d.requerant', 'r')
            ->join('r.personnePhysique', 'pp')
            ->orderBy('e.dateEntree', 'DESC');

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
     * @return BrisPorte[]
     */
    public function getListeDossiersATransmettre(): array
    {
        return $this->createQueryBuilder('d')
            ->join('d.etatDossier', 'ed')
            ->where('ed.etat = :etat')
            ->setParameter('etat', EtatDossierType::DOSSIER_OK_A_INDEMNISER)
            ->getQuery()
            ->getResult();
    }
}
