<?php
namespace App\Contracts;

use App\Entity\BrisPorte;
use App\Entity\BasePrejudice;

interface PrejudiceInterface {
  const DISCRIMINATOR_MAP=['bris_porte' => BrisPorte::class, 'prejudice' => BasePrejudice::class];
  const KEY_MAP=['BRI' => BrisPorte::class, 'PRE' => BasePrejudice::class];

}
