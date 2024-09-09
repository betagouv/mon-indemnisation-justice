<?php
namespace App\Repository;

use App\Contracts\PrejudiceInterface;
use App\Entity\Statut;
use App\Entity\Requerant;

trait PrejudiceRepositoryTrait {

  public function newInstance(Requerant $user): PrejudiceInterface
  {
    /** @var string $classname */
    $classname = $this->getEntityName();
    /** @var EntityManagerInterface $em */
    $em = $this->getEntityManager();
    /** @var PrejudiceInterface $prejudice */
    $prejudice = new $classname();
    $prejudice->setRequerant($user);
    $em->persist($prejudice);
    $em->flush();

    /** @var Statut $statut */
    $statut = new Statut();
    $statut->setEmetteur($user);
    $statut->setPrejudice($prejudice);
    $em->persist($statut);
    $em->flush();

    return $prejudice;
  }


}
