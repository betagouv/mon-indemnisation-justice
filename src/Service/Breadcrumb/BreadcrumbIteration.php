<?php

namespace App\Service\Breadcrumb;

use Symfony\Component\Routing\RouterInterface;

class BreadcrumbIteration {
  private string $_label;
  private ?string $_url = null;
  private array $_translationParams = [];
  /**
   * @param string $label
   * @param string $pathName
   */
  public function __construct(string $label, ?string $pathName=null, array $options=[], array $translationParams=[], RouterInterface $router=null) {
    $this->_label = $label;
    $this->_translationParams = $translationParams;
    if(null !== $pathName)
      $this->_url = $router->generate($pathName, $options);
  }

  public function getUrl(): ?string { return $this->_url;}
  public function getLabel(): string { return $this->_label; }
  public function getTranslationParams(): array { return $this->_translationParams; }
}
