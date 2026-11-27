<?php

namespace MonIndemnisationJustice\Tests\Service\Banque;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Entity\EtablissementBancaire;
use MonIndemnisationJustice\Entity\ReferenceBancaire;
use MonIndemnisationJustice\Service\Banque\GenerateurReleveIdentiteBancaire;
use MonIndemnisationJustice\Service\DocumentManager;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Process\Process;

#[CoversClass(GenerateurReleveIdentiteBancaire::class)]
class GenerateurReleveIdentiteBancaireTest extends KernelTestCase
{
    protected GenerateurReleveIdentiteBancaire $generateur;
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->generateur = self::getContainer()->get(GenerateurReleveIdentiteBancaire::class);
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
    }

    private function texte(ReferenceBancaire $referenceBancaire): string
    {
        $contenu = self::getContainer()->get(DocumentManager::class)->getContenuTexte($referenceBancaire->getDocument());
        $fichier = tempnam(sys_get_temp_dir(), 'rib').'.pdf';
        file_put_contents($fichier, $contenu);

        // Conserver le PDF pour l'inspecter : RIB_PDF=/chemin/fichier.pdf bin/phpunit ...
        if ($destination = getenv('RIB_PDF')) {
            copy($fichier, $destination);
        }

        $process = new Process(['pdftotext', '-layout', $fichier, '-']);
        $process->mustRun();
        unlink($fichier);

        return preg_replace('/\s+/', ' ', $process->getOutput());
    }

    public function testRibAvecLogoEtIbanCalcule(): void
    {
        $referenceBancaire = ReferenceBancaire::depuisRib('30004', '00031', '23456789012', '35', 'Monsieur Jean Dupont')
            ->setNomBanque('BNP Paribas')
            ->setEtablissement($this->em->find(EtablissementBancaire::class, '30004'));
        $this->em->persist($referenceBancaire);

        $document = $this->generateur->generer($referenceBancaire);

        $this->assertSame(DocumentType::TYPE_REFERENCE_BANCAIRE, $document->getType());
        $this->assertSame('application/pdf', $document->getMime());
        $this->assertSame($document, $referenceBancaire->getDocument());
        $this->assertStringStartsWith('data:image/svg+xml;base64,', $this->generateur->variablesGabarit($referenceBancaire)['logo']);

        $texte = $this->texte($referenceBancaire);
        $this->assertStringContainsString('Monsieur Jean Dupont', $texte);
        $this->assertStringContainsString('BNP Paribas', $texte);
        $this->assertStringContainsString('30004 00031 23456789012 35', $texte);
        // IBAN calculé à partir du RIB
        $this->assertStringContainsString('FR76 3000 4000 3123 4567 8901 235', $texte);
    }

    public function testIbanEtrangerSansRib(): void
    {
        $referenceBancaire = ReferenceBancaire::depuisIban('DE89 3704 0044 0532 0130 00', 'Jean Dupont')->setNomBanque('Commerzbank');
        $this->em->persist($referenceBancaire);

        $premier = $this->generateur->generer($referenceBancaire);
        // Une nouvelle génération met à jour le même document
        $this->assertSame($premier, $this->generateur->generer($referenceBancaire));

        $texte = $this->texte($referenceBancaire);
        $this->assertStringContainsString('DE89 3704 0044 0532 0130 00', $texte);
        $this->assertStringNotContainsString('Code guichet', $texte);
        $this->assertNull($this->generateur->variablesGabarit($referenceBancaire)['logo']);
    }
}
