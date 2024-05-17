<?php
namespace App\Service\Version;

class Version {

  private ?string $_branch=null;

  function __construct() {
    $path = realpath(__DIR__.'/../../../.git');
    if($path)
      $this->_branch = str_replace("\n","",preg_replace("/^(.*)\/([^\/]+)$/","$2",file_get_contents($path.'/HEAD')));
  }

  public function getBranch(): ?string { return $this->_branch; }
}
