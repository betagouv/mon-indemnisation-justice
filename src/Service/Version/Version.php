<?php
namespace App\Service\Version;

use FOPG\Component\UtilsBundle\Env\Env;

class Version {

  private ?string $_branch=null;

  function __construct() {
    $path = realpath(__DIR__.'/../../../.git');
    if($path)
      $this->_branch = str_replace("\n","",preg_replace("/^(.*)\/([^\/]+)$/","$2",file_get_contents($path.'/HEAD')));
    if(true !== Env::get('WITH_VERSION'))
      $this->_branch = "";
  }

  public function getBranch(): ?string { return $this->_branch; }
}
