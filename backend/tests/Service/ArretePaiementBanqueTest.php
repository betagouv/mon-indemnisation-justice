<?php

namespace MonIndemnisationJustice\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\ReferenceBancaire;
use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Twig\Environment;

/**
 * Le nom de la banque figure dans l'article 2 de l'arrêté de paiement dès qu'une référence bancaire est connue.
 */
#[CoversNothing]
class ArretePaiementBanqueTest extends KernelTestCase
{
    private const TEMPLATE = 'courrier/arretePaiement/_corps_arrete_paiement.html.twig';

    protected Environment $twig;
    protected Dossier $dossier;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->twig = self::getContainer()->get(Environment::class);
        $this->dossier = current(array_filter(
            self::getContainer()->get(EntityManagerInterface::class)->getRepository(Dossier::class)->findAll(),
            fn (Dossier $dossier) => null !== $dossier->getBrisPorte() && null !== $dossier->getDateDepot()
        ));
    }

    private function rendre(): string
    {
        return $this->twig->render(self::TEMPLATE, ['dossier' => $this->dossier, 'montantIndemnisation' => 1234.56]);
    }

    public function testNomDeLaBanqueConnu(): void
    {
        $this->dossier->setReferenceBancaire(
            ReferenceBancaire::depuisIban('FR7630006000011234567890189', 'Jean Dupont')->setNomBanque('Crédit Agricole')
        );

        $this->assertStringContainsString('ouvert à la banque Crédit Agricole.', $this->rendre());
    }

    public function testBanqueInconnue(): void
    {
        $this->dossier->setReferenceBancaire(null);

        $this->assertStringContainsString('ouvert à la banque ?BANQUE?.', $this->rendre());
    }
}
