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
      new TwigFilter('spellout', [AppRuntime::class,'spellout']),
    ];
  }

  public function getFunctions(): array {
    return [
      new TwigFunction('empty_user', [AppRuntime::class, 'emptyUser']),
    ];
  }
}
