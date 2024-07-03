<?php

namespace App\Repository;

use App\Service\PasswordGenerator;
use App\Contracts\PrejudiceInterface;
use App\Entity\Prejudice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Prejudice>
 *
 * @method Prejudice|null find($id, $lockMode = null, $lockVersion = null)
 * @method Prejudice|null findOneBy(array $criteria, array $orderBy = null)
 * @method Prejudice[]    findAll()
 * @method Prejudice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
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
        parent::__construct($registry, Prejudice::class);
    }

    public function findByStatuts(array $statuts=[],array $orderBy=[],int $offset=0,int $limit=10): array
    {
        if(count($statuts) === 0)
          return [];

        $em = $this->getEntityManager();
        $conn = $em->getConnection();
        $codeStatuts=[];
        foreach($statuts as $statut)
          $codeStatuts[]=$statut->getCode();
        $strCodeStatuts="'".implode("','",$codeStatuts)."'";
        $sql = "
        SELECT p.id,p.discr
        FROM
        (
          SELECT s.prejudice_id,MAX(s.date) date
          FROM public.statut s
          GROUP BY s.prejudice_id
        ) h
        INNER JOIN public.statut s ON h.date = s.date AND s.prejudice_id = h.prejudice_id
        INNER JOIN public.prejudice p ON p.id = s.prejudice_id
        WHERE s.code IN($strCodeStatuts)
        ORDER BY s.date
        OFFSET $offset LIMIT $limit";
        $rec = $conn->executeQuery($sql);
        $tmp=[];
        foreach($rec->fetchAll() as $item) {
          $tmp[]=$em
            ->getRepository(
              PrejudiceInterface::DISCRIMINATOR_MAP[$item['discr']]
            )
            ->find($item['id'])
          ;
        }
        return $tmp;
    }
}
