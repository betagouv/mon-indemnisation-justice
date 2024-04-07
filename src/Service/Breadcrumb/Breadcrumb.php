<?php

namespace App\Service\Breadcrumb;

use Symfony\Component\Routing\RouterInterface;

class Breadcrumb {
  /** @var array<int, BreadcrumbIteration> */
  private array $_items=[];
  /** @var ?RouterInterface */
  private ?RouterInterface $_router=null;
  public function __construct(RouterInterface $router) {
    $this->_router = $router;
  }

  public function add(string $label, ?string $pathName=null, array $options=[], array $translationParams=[]) {
    $this->_items[] = new BreadcrumbIteration(label: $label, pathName: $pathName, options: $options, translationParams: $translationParams, router: $this->_router);
  }

  public function getItems(): array {
    return $this->_items;
  }
}
