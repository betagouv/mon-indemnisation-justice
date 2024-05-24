<?php

namespace App\Repository;

use App\Entity\User;
use App\Entity\Tracking;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tracking>
 *
 * @method Tracking|null find($id, $lockMode = null, $lockVersion = null)
 * @method Tracking|null findOneBy(array $criteria, array $orderBy = null)
 * @method Tracking[]    findAll()
 * @method Tracking[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TrackingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tracking::class);
    }

    public function add(User $user, string $event): void
    {
        $tracking = new Tracking();
        $tracking->setDate(new \DateTime());
        $tracking->setEvent($event);
        $tracking->setAccount($user);
        $this->getEntityManager()->persist($tracking);
        $this->getEntityManager()->flush();
    }
}
