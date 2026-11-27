<?php

namespace MonIndemnisationJustice\Tests\Entity;

use MonIndemnisationJustice\Entity\ReferenceBancaire;
use MonIndemnisationJustice\Entity\TypeReferenceBancaire;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ReferenceBancaire::class)]
class ReferenceBancaireTest extends TestCase
{
    public function testDepuisIbanNormaliseEtExtraitLeCodeBanque(): void
    {
        $reference = ReferenceBancaire::depuisIban(' fr76 3000 4000 0312 3456 7890 143 ', 'Jean Dupont');

        $this->assertSame(TypeReferenceBancaire::IBAN, $reference->getType());
        $this->assertSame('FR7630004000031234567890143', $reference->getIban());
        $this->assertTrue($reference->estIbanFrancais());
        $this->assertSame('30004', $reference->getCodeBanque());
        $this->assertSame('Jean Dupont', $reference->getTitulaireDuCompte());
        $this->assertNull($reference->getNumeroCompte());
    }

    public function testIbanEtrangerSansCodeBanque(): void
    {
        $reference = ReferenceBancaire::depuisIban('DE89 3704 0044 0532 0130 00', 'Jean Dupont');

        $this->assertFalse($reference->estIbanFrancais());
        $this->assertNull($reference->getCodeBanque());
    }

    public function testDepuisRib(): void
    {
        $reference = ReferenceBancaire::depuisRib('30004', '00031', '2345678 90 1', '43', 'Jean Dupont');

        $this->assertSame(TypeReferenceBancaire::RIB, $reference->getType());
        $this->assertSame('30004', $reference->getCodeBanque());
        $this->assertSame('00031', $reference->getCodeGuichet());
        $this->assertSame('2345678901', $reference->getNumeroCompte());
        $this->assertSame('43', $reference->getCleRib());
        $this->assertNull($reference->getIban());
        $this->assertFalse($reference->estIbanFrancais());
    }
}
