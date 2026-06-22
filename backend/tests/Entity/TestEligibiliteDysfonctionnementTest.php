<?php

namespace MonIndemnisationJustice\Tests\Entity;

use MonIndemnisationJustice\Entity\TestEligibiliteDysfonctionnement;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
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
            'piecesProcedure' => ['acte_introductif', 'ecritures'],
            'preuvesDiligences' => true,
        ];
    }

    public function testEstEligibleQuandTousLesCriteresRemplis(): void
    {
        $test = TestEligibiliteDysfonctionnement::fromArray($this->eligibleParDefaut());

        $this->assertTrue($test->estEligible());
    }

    public function testEstEligibleAvecPlusieursDecisions(): void
    {
        $test = TestEligibiliteDysfonctionnement::fromArray([
            ...$this->eligibleParDefaut(),
            'typesDecision' => ['jugement_premiere_instance', 'arret_cour_appel'],
        ]);

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

    public function testNonEligibleSiTypesDecisionContientAucune(): void
    {
        $test = TestEligibiliteDysfonctionnement::fromArray([
            ...$this->eligibleParDefaut(),
            'typesDecision' => ['aucune'],
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

    public function testNonEligibleSiPiecesProcedureContientAucune(): void
    {
        $test = TestEligibiliteDysfonctionnement::fromArray([
            ...$this->eligibleParDefaut(),
            'piecesProcedure' => ['aucune'],
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

    public static function donneesPrescriptionExpirationLe1erJanvier(): array
    {
        $anneeExpiree = (int) date('Y') - 5; // ex. 2021 → expire le 1er jan 2026 → prescrit
        $anneeValide = (int) date('Y') - 4;  // ex. 2022 → expire le 1er jan 2027 → dans les délais

        return [
            'annee_expiree' => [new \DateTimeImmutable("{$anneeExpiree}-12-31"), false],
            'annee_valide' => [new \DateTimeImmutable("{$anneeValide}-01-01"), true],
        ];
    }

    #[DataProvider('donneesPrescriptionExpirationLe1erJanvier')]
    public function testPrescriptionExpirationLe1erJanvier(\DateTimeImmutable $dateDecision, bool $estDansLesDelais): void
    {
        $test = TestEligibiliteDysfonctionnement::fromArray([
            ...$this->eligibleParDefaut(),
            'dateDecision' => $dateDecision,
        ]);

        $this->assertSame($estDansLesDelais, $test->estEligible());
    }
}
