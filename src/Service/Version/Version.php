<?php
namespace App\Service\Version;

class Version {

  private ?string $_branch=null;

  function __construct() {
    $this->_branch = $_ENV['COMMIT_ID'] ?? null;
  }

  public function getBranch(): ?string { return $this->_branch; }
}
