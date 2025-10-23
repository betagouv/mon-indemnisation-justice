<?php

namespace MonIndemnisationJustice\Tests\Service;

use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Repository\BrisPorteRepository;
use MonIndemnisationJustice\Service\DossierManager;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class DossierManagerTest extends WebTestCase
{
    public function testAvancerDossier(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        /** @var BrisPorteRepository $dossierRepository */
        $dossierRepository = $container->get(BrisPorteRepository::class);

        /** @var DossierManager $dossierManager */
        $dossierManager = $container->get(DossierManager::class);

        $dossiersAFinaliser = $dossierRepository->listerDossierParEtat(EtatDossierType::DOSSIER_A_FINALISER);

        foreach ($dossiersAFinaliser as $index => $dossierAFinaliser) {
            $dossierManager->avancer($dossierAFinaliser);

            $this->assertEquals(EtatDossierType::DOSSIER_A_ATTRIBUER, $dossierAFinaliser->getEtatDossier()->getEtat());
            $this->assertEquals(
                'BRI/'.(new \DateTime())->format('Ymd').'/'.str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                $dossierAFinaliser->getReference()
            );
        }
    }
}
