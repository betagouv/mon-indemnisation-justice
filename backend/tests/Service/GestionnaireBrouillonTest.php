<?php

namespace MonIndemnisationJustice\Tests\Service;

use MonIndemnisationJustice\Api\Requerant\Brouillon\Dto\DossierDto;
use MonIndemnisationJustice\Entity\Brouillon;
use MonIndemnisationJustice\Entity\BrouillonType;
use MonIndemnisationJustice\Service\GestionnaireBrouillon;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GestionnaireBrouillonTest extends KernelTestCase
{
    public function testExtraireEntiteTravailWithNumericArrayKeys()
    {
        self::bootKernel();
        $container = static::getContainer();

        /** @var GestionnaireBrouillon $gestionnaire */
        $gestionnaire = $container->get(GestionnaireBrouillon::class);

        // Test data with numeric array keys (simulating the issue)
        $testData = [
            'usager' => 1,
            'personnePhysique' => [
                'personne' => [
                    'id' => 'be9f1b28-11e5-4023-9ca4-2c7a56da6fb1',
                    'civilite' => 'M',
                    'nom' => 'ERRANT',
                    'nomNaissance' => 'ERRANT',
                    'prenom' => 'Rick',
                    'courriel' => 'rick.errant@courriel.fr',
                ],
                'adresse' => [
                    'ligne1' => '25 allée des Mimosas',
                    'codePostal' => '44200',
                    'commune' => 'Nantes',
                ],
                'dateNaissance' => '1982-03-13',
                'paysNaissance' => [
                    'code' => 'FRA',
                    'nom' => 'France',
                ],
                'communeNaissance' => [
                    'id' => 33976,
                    'codePostal' => '88300',
                    'nom' => 'Neufchâteau',
                    'departement' => 'Vosges',
                ],
            ],
            'rapportAuLogement' => 'BAILLEUR',
            'adresse' => [
                'ligne1' => '12 rue des Oliviers',

                'codePostal' => '44100',
                'commune' => 'Nantes ',
            ],
            'dateOperation' => '2026-01-28',
            'idTestEligibilite' => 2132,
            'estPorteBlindee' => false,
            'piecesJointes' => [
                [ // This numeric key was causing the issue
                    'id' => 5146,
                    'type' => 'attestation_information',
                    'chemin' => '54b10940536c05249fde10e142bb594614a1d777c0da4d4299dfbc292b81fb2a.pdf',
                    'nom' => 'Attestation d\'information d\'un préjudice.pdf',
                    'mime' => 'application/pdf',
                    'taille' => 85276,
                ],
            ],
        ];

        // Create a mock brouillon
        $brouillon = new Brouillon();
        $brouillon->setType(BrouillonType::BROUILLON_DOSSIER_BRIS_PORTE);
        $brouillon->setDonnees($testData);

        try {
            $result = $gestionnaire->extraireEntiteTravail($brouillon);
            $this->assertInstanceOf(DossierDto::class, $result);
            $this->assertCount(2, $result->piecesJointes);
            $this->assertEquals(5146, $result->piecesJointes[0]->id);
            $this->assertEquals(5147, $result->piecesJointes[1]->id);
        } catch (\Exception $e) {
            $this->fail('Denormalization failed: '.$e->getMessage());
        }
    }
}
