<?php

namespace App\Tests\Functional;

use Symfony\Component\Panther\PantherTestCase;

abstract class AbstractFunctionalTestCase extends PantherTestCase
{
    public function devices(): array
    {
        return [
            'mobile' => ['mobile', 428, 926],
            'desktop' => ['desktop', 1200, 2000],
        ];
    }
}