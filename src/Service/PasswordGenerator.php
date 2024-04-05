<?php

namespace App\Service;

class PasswordGenerator {
  public static function new(int $length=16): string {

    $bytes = random_bytes(ceil($length/2));
    return  bin2hex($bytes);
  }
}
