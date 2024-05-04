<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    use CommonActionTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    public function findAdminFoncs(): array
    {
      $usernames = $this->findAdminUsernames();
      return $this->findBy(['username' => $usernames]);
    }

    public function findAdminUsernames(): array
    {
      $output = [];
      $users = $this->findAll();
      foreach($users as $user) {
        if(true === $user->isAdminFonc())
          $output[]=$user->getUsername();
      }
      return $output;
    }

    public function findByRoles(array $roles): array
    {
      $dql = "
      SELECT u
      FROM App\Entity\User u
      WHERE CONTAINS(TO_JSONB(u.roles), :role0) = TRUE
      OR CONTAINS(TO_JSONB(u.roles), :role1) = TRUE
      OR CONTAINS(TO_JSONB(u.roles), :role2) = TRUE
      ";

      $role0 = '["'.($roles[0]??'NONE').'"]';
      $role1 = '["'.($roles[1]??'NONE').'"]';
      $role2 = '["'.($roles[2]??'NONE').'"]';

      $query  = $this->getEntityManager()
        ->createQuery($dql)
        ->setParameter('role0', $role0)
        ->setParameter('role1', $role1)
        ->setParameter('role2', $role2)
      ;
      return $query->getResult();
    }
}
