<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Tests\Repository;

use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Repository\BrisPorteRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class BrisPorteRepositoryTest extends WebTestCase
{
    public function testCalculerReference(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        /** @var BrisPorteRepository $dossierRepository */
        $dossierRepository = $container->get(BrisPorteRepository::class);
        $dossiersAAttribuer = $dossierRepository->listerDossierParEtat(EtatDossierType::DOSSIER_A_ATTRIBUER);

        $this->assertCount(1, $dossiersAAttribuer);
        $this->assertEquals(
            'BRI/'.$dossiersAAttribuer[0]->getDateDeclaration()->format('Ymd').'/002',
            $dossierRepository->calculerReference($dossiersAAttribuer[0])
        );
    }
}
