<?php

namespace MonIndemnisationJustice\Tests\Service;

use MonIndemnisationJustice\Service\GenerateurCodeInvitation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GenerateurCodeInvitation::class)]
class GenerateurCodeInvitationTest extends TestCase
{
    /**
     * ETQ système, le code d'invitation doit faire 32 caractères hexadécimaux majuscules (128 bits).
     */
    public function testCodeDe32CaracteresHexadecimauxMajuscules(): void
    {
        $code = new GenerateurCodeInvitation()->generer();

        $this->assertMatchesRegularExpression('/^[A-F0-9]{32}$/', $code);
    }

    /**
     * ETQ système, deux codes générés successivement ne doivent pas être identiques.
     */
    public function testCodesDistincts(): void
    {
        $generateur = new GenerateurCodeInvitation();

        $codes = [];
        for ($i = 0; $i < 1000; ++$i) {
            $codes[] = $generateur->generer();
        }

        $this->assertCount(1000, array_unique($codes));
    }
}
