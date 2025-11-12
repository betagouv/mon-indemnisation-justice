<?php

namespace MonIndemnisationJustice\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\BrisPorte;
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
    protected EntityManagerInterface $em;
    protected BrisPorteRepository $dossierRepository;
    protected DossierManager $dossierManager;

    public function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);
        $this->dossierRepository = $container->get(BrisPorteRepository::class);
        $this->dossierManager = $container->get(DossierManager::class);
    }

    public function testAvancerDossier(): void
    {
        $dossiersAFinaliser = $this->dossierRepository->listerDossierParEtat(EtatDossierType::DOSSIER_A_FINALISER);

        foreach ($dossiersAFinaliser as $index => $dossierAFinaliser) {
            $this->dossierManager->avancer($dossierAFinaliser);

            $this->assertEquals(EtatDossierType::DOSSIER_A_ATTRIBUER, $dossierAFinaliser->getEtatDossier()->getEtat());
            $this->assertEquals(
                'BRI/'.(new \DateTime())->format('Ymd').'/'.str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                $dossierAFinaliser->getReference()
            );
        }
    }

    public function testAnnulerOk(): void
    {
        /** @var BrisPorte $dossierOkASigner */
        $dossierOkASigner = $this->dossierRepository->listerDossierParEtat(EtatDossierType::DOSSIER_OK_A_SIGNER)[0];
        $nbEtats = $dossierOkASigner->getHistoriqueEtats()->count();
        $this->dossierManager->annuler($dossierOkASigner);

        $this->em->refresh($dossierOkASigner);

        $this->assertEquals($nbEtats - 1, $dossierOkASigner->getHistoriqueEtats()->count());
        $this->assertEquals(EtatDossierType::DOSSIER_EN_INSTRUCTION, $dossierOkASigner->getEtatDossier()->getEtat());
    }

    public function testRevenirOk(): void
    {
        /** @var BrisPorte $dossierOkASigner */
        $dossierOkASigner = $this->dossierRepository->listerDossierParEtat(EtatDossierType::DOSSIER_OK_A_SIGNER)[0];
        $nbEtats = $dossierOkASigner->getHistoriqueEtats()->count();
        $this->dossierManager->revenir($dossierOkASigner, 2);

        $this->em->refresh($dossierOkASigner);

        $this->assertEquals($nbEtats + 1, $dossierOkASigner->getHistoriqueEtats()->count());
        $this->assertEquals(EtatDossierType::DOSSIER_A_INSTRUIRE, $dossierOkASigner->getEtatDossier()->getEtat());
        $this->assertLessThanOrEqual(1, $dossierOkASigner->getEtatDossier()->getDate()->getTimestamp() - (new \DateTime())->getTimestamp());

        $this->dossierManager->revenir($dossierOkASigner, 2);

        $this->em->refresh($dossierOkASigner);

        $this->assertEquals($nbEtats + 2, $dossierOkASigner->getHistoriqueEtats()->count());
        $this->assertEquals(EtatDossierType::DOSSIER_EN_INSTRUCTION, $dossierOkASigner->getEtatDossier()->getEtat());
        $this->assertLessThanOrEqual(1, $dossierOkASigner->getEtatDossier()->getDate()->getTimestamp() - (new \DateTime())->getTimestamp());
    }

    public function testRevenirKo(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('Impossible de revenir plus de 1 état(s) en arrière');

        /** @var BrisPorte $dossierOkASigner */
        $dossierAAttribuer = $this->dossierRepository->listerDossierParEtat(EtatDossierType::DOSSIER_A_ATTRIBUER)[0];
        $this->dossierManager->revenir($dossierAAttribuer, 2);
    }
}
