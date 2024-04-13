<?php

namespace App\Service\Breadcrumb;

use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class Breadcrumb {
  /** @var array<int, BreadcrumbIteration> */
  private array $_items=[];
  /** @var ?RouterInterface */
  private ?RouterInterface $_router=null;
  private ?TranslatorInterface $_translator=null;

  public function __construct(RouterInterface $router, TranslatorInterface $translator) {
    $this->_router = $router;
    $this->_translator=$translator;
  }

  public function add(string $label, ?string $pathName=null, array $options=[], array $translationParams=[]) {
    $this->_items[] = new BreadcrumbIteration(
      label: $label,
      pathName: $pathName,
      options: $options,
      translationParams: $translationParams,
      router: $this->_router,
      translator: $this->_translator
    );
  }

  public function getItems(): array {
    return $this->_items;
  }
}
