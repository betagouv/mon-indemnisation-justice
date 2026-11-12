<?php

namespace MonIndemnisationJustice\Tests\Service\Signature;

use MonIndemnisationJustice\Service\Signature\GestionnaireSignatureRequerantLegalySpace;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class FormaterTelephoneTest extends TestCase
{
    public static function fournirTelephones(): array
    {
        return [
            // Mobiles français, saisies nationales
            'national compact 06' => ['0612345678', '+33 612345678'],
            'national compact 07' => ['0712345678', '+33 712345678'],
            'national espacé' => ['06 12 34 56 78', '+33 612345678'],
            'national avec points' => ['06.12.34.56.78', '+33 612345678'],
            'national avec tirets' => ['06-12-34-56-78', '+33 612345678'],
            // Mobiles français, saisies internationales
            'international +33' => ['+33612345678', '+33 612345678'],
            'international +33 espacé' => ['+33 6 12 34 56 78', '+33 612345678'],
            'international 0033' => ['0033712345678', '+33 712345678'],
            'international avec 0 national' => ['+33 (0)6 12 34 56 78', '+33 612345678'],
            // Rejetés : absents, fixes, spéciaux, étrangers ou mal formés
            'null' => [null, null],
            'vide' => ['', null],
            'fixe' => ['01 23 45 67 89', null],
            'fixe international' => ['+33 1 23 45 67 89', null],
            'numéro 08' => ['0812345678', null],
            'numéro 09' => ['0912345678', null],
            'trop court' => ['061234567', null],
            'trop long' => ['06123456789', null],
            'étranger' => ['+32 470 12 34 56', null],
            'texte' => ['pas de téléphone', null],
        ];
    }

    #[DataProvider('fournirTelephones')]
    public function testFormaterTelephone(?string $saisie, ?string $attendu): void
    {
        $this->assertSame($attendu, GestionnaireSignatureRequerantLegalySpace::formaterTelephone($saisie));
    }
}
