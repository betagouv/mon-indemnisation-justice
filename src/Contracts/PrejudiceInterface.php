<?php
namespace App\Contracts;

use App\Entity\BrisPorte;
use App\Entity\Prejudice;

interface PrejudiceInterface {
  const DISCRIMINATOR_MAP=['bris_porte' => BrisPorte::class, 'prejudice' => Prejudice::class];
  const KEY_MAP=['BRI' => BrisPorte::class, 'PRE' => Prejudice::class];

}