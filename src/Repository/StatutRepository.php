<?php

namespace App\Repository;

use App\Contracts\PrejudiceInterface;
use App\Entity\EtatBrisPorte;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<EtatBrisPorte>
 *
 * @method EtatBrisPorte|null find($id, $lockMode = null, $lockVersion = null)
 * @method EtatBrisPorte|null findOneBy(array $criteria, array $orderBy = null)
 * @method EtatBrisPorte[]    findAll()
 * @method EtatBrisPorte[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StatutRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EtatBrisPorte::class);
    }

    public function addEvent(PrejudiceInterface $prejudice, UserInterface $user, string $code): static
    {
        $statut = (new EtatBrisPorte())
            ->setCode($code)
            ->setPrejudice($prejudice)
            ->setEmetteur($user);

        $this->getEntityManager()->persist($statut);
        $this->getEntityManager()->flush();

        return $this;
    }
}
