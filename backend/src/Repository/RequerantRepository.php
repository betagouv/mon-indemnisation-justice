<?php

namespace MonIndemnisationJustice\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use MonIndemnisationJustice\Entity\Requerant;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<Requerant>
 *
 * @method Requerant|null find($id, $lockMode = null, $lockVersion = null)
 * @method Requerant|null findOneBy(array $criteria, array $orderBy = null)
 * @method Requerant[]    findAll()
 * @method Requerant[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RequerantRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Requerant::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Requerant) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findByEmailOrSub(?string $email, ?string $sub): ?Requerant
    {
        return $this
            ->createQueryBuilder('u')
            ->where('u.email = :email')
            ->orWhere('u.sub = :sub')
            ->setParameter('email', $email)
            ->setParameter('sub', $sub)->getQuery()->getOneOrNullResult();
    }

    public function findByRoles(array $roles): array
    {
        $qb = $this
            ->createQueryBuilder('u');

        foreach ($roles as $index => $role) {
            $qb->orWhere("u.roles LIKE :role$index");
            $qb->setParameter("role$index", "%$role%");
        }

        return $qb->getQuery()->getResult();
    }
}
