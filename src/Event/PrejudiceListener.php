<?php

namespace MonIndemnisationJustice\Event;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use MonIndemnisationJustice\Entity\BrisPorte;

#[AsDoctrineListener(event: Events::preUpdate, priority: 500, connection: 'default')]
class PrejudiceListener
{
    /**
     * Crée.
     *
     * @throws \Doctrine\DBAL\Exception
     */
    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        if ($entity instanceof BrisPorte) {
            if (null !== $entity->getDateDeclaration() && null === $entity->getReference()) {
                $entityManager = $args->getObjectManager();
                $conn = $entityManager->getConnection();

                $req = $conn->executeQuery(<<<SQL
SELECT count(p.id) + 1 cpt
FROM bris_porte p
    INNER JOIN public.dossier_etats ed ON ed.id = p.etat_actuel_id AND ed.etat <> 'DOSSIER_INITIE' AND to_char(ed.date, 'yyyymmdd') = :date
SQL,
                    [
                        'date' => (new \DateTime())->format('Ymd'),
                    ]
                );

                $cpt = $req->fetchOne() ?? 1;

                $entity->setReference(
                    sprintf(
                        '%s/%s/%s',
                        $entity->getType()->value,
                        $entity->getDateDeclaration()->format('Ymd'),
                        str_pad($cpt, 3, '0', STR_PAD_LEFT)
                    )
                );

                /**
                 * @author yanroussel
                 *         Ajout d'un numéro de suivi
                 */
                $raccourci = $entityManager
                  ->getRepository(BrisPorte::class)
                  ->generateRaccourci()
                ;
                $entity->setRaccourci($raccourci);
            }
        }
    }
}
