<?php

namespace App\EventListener;

use App\Entity\BasePrejudice;
use App\Entity\BrisPorte;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;

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

                $sql = "
            SELECT count(*)+1 cpt
            FROM public.bris_porte p
            WHERE date_declaration = '".$entity->getDateDeclaration()->format('Y-m-d')."'";
                $req = $conn->executeQuery($sql);

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
