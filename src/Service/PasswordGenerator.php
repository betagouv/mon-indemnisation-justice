<?php

namespace MonIndemnisationJustice\Service;

class PasswordGenerator {
  public static function new(int $length=16,bool $withSpecialChars=true): string {
    $specialChars = ['&',':','-','$'];

    $bytes = random_bytes(ceil($length/2));
    $tmp =  bin2hex($bytes);
    if(true === $withSpecialChars) {
      $randTmp = rand(0,strlen($tmp)-1);
      $randSC = rand(0,count($specialChars)-1);
      $tmp[$randTmp]=$specialChars[$randSC];
    }
    return $tmp;
  }
}
