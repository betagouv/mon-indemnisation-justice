<?php

namespace App\Repository;

use App\Contracts\PrejudiceInterface;
use App\Entity\Statut;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Statut>
 *
 * @method Statut|null find($id, $lockMode = null, $lockVersion = null)
 * @method Statut|null findOneBy(array $criteria, array $orderBy = null)
 * @method Statut[]    findAll()
 * @method Statut[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StatutRepository extends ServiceEntityRepository
{
    use CommonActionTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Statut::class);
    }

    public function addEvent(PrejudiceInterface $prejudice, UserInterface $user, string $code): static
    {
        $statut = new Statut();
        $statut->setCode($code);
        $statut->setPrejudice($prejudice);
        $statut->setEmetteur($user);
        $this->add($statut,true);
        return $this;
    }
}
