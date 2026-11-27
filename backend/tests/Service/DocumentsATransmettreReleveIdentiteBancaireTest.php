<?php

namespace MonIndemnisationJustice\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\ReferenceBancaire;
use MonIndemnisationJustice\Service\Banque\GenerateurReleveIdentiteBancaire;
use MonIndemnisationJustice\Service\DocumentManager;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Process\Process;

/**
 * Le relevé d'identité bancaire transmis au bureau du budget est celui généré d'après les coordonnées saisies, à défaut
 * ceux téléversés par le requérant.
 */
#[CoversClass(DocumentManager::class)]
class DocumentsATransmettreReleveIdentiteBancaireTest extends KernelTestCase
{
    private const FICHIER_RIB = "Relevé d'identité bancaire.pdf";

    protected DocumentManager $documentManager;
    protected Dossier $dossier;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->documentManager = self::getContainer()->get(DocumentManager::class);
        $this->dossier = current(array_filter(
            self::getContainer()->get(EntityManagerInterface::class)->getRepository(Dossier::class)->findAll(),
            fn (Dossier $dossier) => null !== $dossier->getReference()
        ));
        // RIB téléversé par le requérant
        $this->documentManager->ajouterFichierLocal($this->dossier, __DIR__.'/../ressources/pieces_jointes/Facture 2.pdf', DocumentType::TYPE_RIB);
    }

    private function contenuRibTransmis(): ?string
    {
        $zip = $this->documentManager->genererListeDocumentsATransmettre($this->dossier);
        $contenu = $zip->getFromName(self::FICHIER_RIB);
        $zip->close();

        return false !== $contenu ? $contenu : null;
    }

    private static function texte(string $pdf): string
    {
        $fichier = tempnam(sys_get_temp_dir(), 'zip').'.pdf';
        file_put_contents($fichier, $pdf);
        $process = new Process(['pdftotext', $fichier, '-']);
        $process->mustRun();
        unlink($fichier);

        return $process->getOutput();
    }

    public function testPrefereLeReleveGenere(): void
    {
        $referenceBancaire = ReferenceBancaire::depuisIban('FR7630004000031234567890143', 'Titulaire Genere')->setNomBanque('BNP Paribas');
        $this->dossier->setReferenceBancaire($referenceBancaire);
        self::getContainer()->get(GenerateurReleveIdentiteBancaire::class)->generer($referenceBancaire, $this->dossier);

        $this->assertStringContainsString('Titulaire Genere', self::texte($this->contenuRibTransmis()));
    }

    public function testUtiliseLesRibTeleversesADefaut(): void
    {
        $this->dossier->setReferenceBancaire(null);

        $contenu = $this->contenuRibTransmis();

        $this->assertNotNull($contenu);
        $this->assertStringNotContainsString('Titulaire Genere', self::texte($contenu));
    }
}
