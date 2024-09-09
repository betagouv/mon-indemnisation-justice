<?php

namespace App\Repository;

use App\Service\PasswordGenerator;
use App\Contracts\PrejudiceInterface;
use App\Entity\BasePrejudice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BasePrejudice>
 *
 * @method BasePrejudice|null find($id, $lockMode = null, $lockVersion = null)
 * @method BasePrejudice|null findOneBy(array $criteria, array $orderBy = null)
 * @method BasePrejudice[]    findAll()
 * @method BasePrejudice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PrejudiceRepository extends ServiceEntityRepository
{
    public function generate_raccourci(int $length=8): string
    {
        $password = mb_strtoupper(PasswordGenerator::new(length: $length,withSpecialChars: false));
        $conn = $this->getEntityManager()->getConnection();
        $sql = "SELECT id FROM public.prejudice WHERE raccourci=:raccourci";
        $req = $conn->prepare($sql);
        do {
          $req->bindParam("raccourci",$password);
          $stmt = $req->execute();
          $result = $stmt->fetchOne();
        } while ($result);
        return $password;
    }

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BasePrejudice::class);
    }

    public function findByStatuts(array $statuts=[],array $orderBy=[],int $offset=0,int $limit=10): array
    {
        return $this->findAll();
    }
}
