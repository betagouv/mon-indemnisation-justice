<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Tests\Service\DataGouv;

use MonIndemnisationJustice\Service\DataGouv\ImporteurGeoCodePostal;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ImporteurGeoCodeTest extends WebTestCase
{
    public function testNormaliserNom(): void
    {
        // (1) boot the Symfony kernel
        self::bootKernel();

        // (2) use static::getContainer() to access the service container
        $container = static::getContainer();

        $importeurGeoCodePostal = $container->get(ImporteurGeoCodePostal::class);

        $this->assertEquals("L'Abergement-Clemenciat", $importeurGeoCodePostal->normaliserNom('L ABERGEMENT CLEMENCIAT'));
        $this->assertEquals('Marseille', $importeurGeoCodePostal->normaliserNom('MARSEILLE 16'));
        $this->assertEquals("Pont-l'Eveque", $importeurGeoCodePostal->normaliserNom('PONT L EVEQUE'));
    }
}
