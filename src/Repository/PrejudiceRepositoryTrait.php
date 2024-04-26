<?php
namespace App\Repository;

use App\Contracts\PrejudiceInterface;
use App\Entity\Categorie;
use App\Entity\Statut;
use App\Entity\User;

trait PrejudiceRepositoryTrait {

  public function newInstance(User $user): PrejudiceInterface
  {
    /** @var string $classname */
    $classname = $this->getEntityName();
    /** @var EntityManagerInterface $em */
    $em = $this->getEntityManager();
    /** @var Categorie $categorie */
    $categorie = $em
      ->getRepository(Categorie::class)
      ->findOneBy(['mnemo' => Categorie::MNEMO_BRIS_PORTE])
    ;
    /** @var PrejudiceInterface $prejudice */
    $prejudice = new $classname();
    $prejudice->setRequerant($user);
    $prejudice->setCategorie($categorie);
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
