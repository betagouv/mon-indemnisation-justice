<?php

namespace App\Twig;

use Symfony\Component\Intl\Intl;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class AppExtension extends AbstractExtension
{
  public function getFilters(): array {
    return [
      new TwigFilter('translate', [AppRuntime::class,'translate']),
    ];
  }

  public function getFunctions(): array {
    return [
    ];
  }
}
