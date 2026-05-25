<?php

namespace Service;

use MonIndemnisationJustice\Service\MontantAfficheur;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

#[CoversClass(MontantAfficheur::class)]
class MontantAfficheurTest extends WebTestCase
{
    protected MontantAfficheur $montantAfficheur;

    public function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        $this->montantAfficheur = $container->get(MontantAfficheur::class);
    }

    public function testAfficherMontantLitteral(): void
    {
        $this->assertEquals('cent vingt-trois euros et dix-sept centimes', $this->montantAfficheur->afficherMontantLitteral(123.17));
        $this->assertEquals('dix-huit euros', $this->montantAfficheur->afficherMontantLitteral(18.));
        $this->assertEquals('zéro euro', $this->montantAfficheur->afficherMontantLitteral(0.));
    }
}
