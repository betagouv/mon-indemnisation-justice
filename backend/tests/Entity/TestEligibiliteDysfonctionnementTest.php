<?php

namespace MonIndemnisationJustice\Tests\Entity;

use MonIndemnisationJustice\Entity\TestEligibiliteDysfonctionnement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TestEligibiliteDysfonctionnement::class)]
class TestEligibiliteDysfonctionnementTest extends TestCase
{
    private function eligibleParDefaut(): array
    {
        return [
            'procedureTerminee' => true,
            'dateDecision' => new \DateTimeImmutable(date('Y', strtotime('-1 year')) . '-06-15'),
            'aUneActionContentieuse' => false,
            'typesDecision' => ['jugement_premiere_instance'],
            'piecesProcedure' => ['assignation', 'ecritures'],
            'preuvesDiligences' => true,
        ];
    }

    public function testEstEligibleQuandTousLesCriteresRemplis(): void
    {
        $test = TestEligibiliteDysfonctionnement::fromArray($this->eligibleParDefaut());

        $this->assertTrue($test->estEligible());
    }

    public function testNonEligibleSiDatePrescrite(): void
    {
        $test = TestEligibiliteDysfonctionnement::fromArray([
            ...$this->eligibleParDefaut(),
            'dateDecision' => new \DateTimeImmutable('2010-06-15'), // expiration 1er jan 2015
        ]);

        $this->assertFalse($test->estEligible());
    }

    public function testNonEligibleSiActionContentieuseEnCours(): void
    {
        $test = TestEligibiliteDysfonctionnement::fromArray([
            ...$this->eligibleParDefaut(),
            'aUneActionContentieuse' => true,
        ]);

        $this->assertFalse($test->estEligible());
    }

    public function testNonEligibleSiAucuneDecisionDeJustice(): void
    {
        $test = TestEligibiliteDysfonctionnement::fromArray([
            ...$this->eligibleParDefaut(),
            'typesDecision' => [],
        ]);

        $this->assertFalse($test->estEligible());
    }

    public function testNonEligibleSiAucunePieceDeProcedure(): void
    {
        $test = TestEligibiliteDysfonctionnement::fromArray([
            ...$this->eligibleParDefaut(),
            'piecesProcedure' => [],
        ]);

        $this->assertFalse($test->estEligible());
    }

    public function testNonEligibleSansPreuveDeDiligences(): void
    {
        $test = TestEligibiliteDysfonctionnement::fromArray([
            ...$this->eligibleParDefaut(),
            'preuvesDiligences' => false,
        ]);

        $this->assertFalse($test->estEligible());
    }

    public function testNonEligibleSiDateDecisionAbsente(): void
    {
        $test = TestEligibiliteDysfonctionnement::fromArray([
            ...$this->eligibleParDefaut(),
            'dateDecision' => null,
        ]);

        $this->assertFalse($test->estEligible());
    }

    public function testPrescriptionExpirationLe1erJanvier(): void
    {
        $anneeExpirée = (int) date('Y') - 5; // ex. 2021 → expire le 1er jan 2026 → prescrit
        $anneeValide = (int) date('Y') - 4;  // ex. 2022 → expire le 1er jan 2027 → dans les délais

        // Décision fin de l'année expirée → prescrite (peu importe le jour dans l'année)
        $test = TestEligibiliteDysfonctionnement::fromArray([
            ...$this->eligibleParDefaut(),
            'dateDecision' => new \DateTimeImmutable("{$anneeExpirée}-12-31"),
        ]);
        $this->assertFalse($test->estEligible());

        // Décision début de l'année valide → encore dans les délais
        $test = TestEligibiliteDysfonctionnement::fromArray([
            ...$this->eligibleParDefaut(),
            'dateDecision' => new \DateTimeImmutable("{$anneeValide}-01-01"),
        ]);
        $this->assertTrue($test->estEligible());
    }
}
