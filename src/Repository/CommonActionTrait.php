<?php
namespace App\Repository;

use App\Contracts\EntityInterface;

trait CommonActionTrait {
  public function add(EntityInterface $entity, bool $flush=false): void {
    $em = $this->getEntityManager();
    $em->persist($entity);
    if(true === $flush)
      $em->flush();
  }

  public function remove(EntityInterface $entity, bool $flush=false): void {
    $em = $this->getEntityManager();
    $em->remove($entity);
    if(true===$flush)
      $em->flush();
  }
}
