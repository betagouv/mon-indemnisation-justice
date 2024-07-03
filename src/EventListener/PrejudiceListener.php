<?php
namespace App\EventListener;

use App\Contracts\PrejudiceInterface;
use App\Entity\Prejudice;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::postPersist, priority: 500, connection: 'default')]
class PrejudiceListener
{
    // the listener methods receive an argument which gives you access to
    // both the entity object of the event and the entity manager itself
    public function postPersist(PostPersistEventArgs $args): void
    {
        $entity = $args->getObject();
        /** @var array $interfaces */
        $interfaces = class_implements($entity);
        if (!in_array(PrejudiceInterface::class,$interfaces))
            return;

        $entityManager = $args->getObjectManager();
        $conn = $entityManager->getConnection();
        $sql = "
        SELECT count(*)+1 cpt
        FROM public.prejudice p
        WHERE date_declaration = '".$entity->getDateDeclaration()->format('Y-m-d')."'";
        $req = $conn->executeQuery($sql);
        $cpt = $req->fetch()['cpt'];
        $reference = [
          array_search(get_class($entity),PrejudiceInterface::KEY_MAP),
          $entity->getDateDeclaration()->format('Ymd'),
          str_pad($cpt,3,"0",STR_PAD_LEFT)
        ];

        $entity->setReference(implode("/",$reference));

        /**
         * @author yanroussel
         *         Ajout d'un numéro de suivi
         */
        $raccourci = $entityManager
          ->getRepository(Prejudice::class)
          ->generate_raccourci()
        ;
        $entity->setRaccourci($raccourci);

        $entityManager->flush();
    }
}
