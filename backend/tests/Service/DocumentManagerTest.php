<?php

namespace MonIndemnisationJustice\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Service\DocumentManager;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

class DocumentManagerTest extends WebTestCase
{
    protected EntityManagerInterface $em;
    protected DocumentManager $documentManager;

    public function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);
        $this->documentManager = $container->get(DocumentManager::class);
    }

    static function donneesGenererCourrierRejetOk(): array
    {
        return [
            'est_vise' => [
                'est_vise',
                8,
                [
                    [3, "l’opération de police judiciaire ayant conduit au bris de porte du domicile visait à l’interpellation de"],
                    [4, "l'instruction de votre demande n'a pas permis de mettre en évidence un dysfonctionnement du service public de la justice"],
                ]
            ],
            'est_hebergeant' => [
                'est_hebergeant',
                8,
                [
                    [2, "l’article 7 de la Loi n° 89-462 du 6 juillet 1989 impose au locataire d’user paisiblement des locaux conformément à leur destination contractuelle et de s'assurer contre les risques dont il doit répondre en tant que locataire"],
                    [3, "hébergé à votre domicile, sans qu'il n'y ait eu d'erreur de porte de la part des"],
                ]
            ],
            'est_bailleur' => [
                'est_bailleur',
                8,
                [
                    [2, "l’article 7 de la Loi n° 89-462 du 6 juillet 1989 impose au locataire d’user paisiblement des locaux conformément à leur destination contractuelle et de s'assurer contre les risques dont il doit répondre en tant que locataire"],
                    [3, "il appartient à votre locataire de répondre des dommages causés engageant sa responsabilité contractuelle"],
                ]
            ],
        ];
    }

    /**
     * @dataProvider donneesGenererCourrierRejetOk
     *
     * @param string $motifRejet
     * @param int $nbParagraphes
     * @param array $elements
     *
     * @return void
     */
    public function testGenererCourrierRejetOk(string $motifRejet, int $nbParagraphes, array $elements): void
    {
        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_EN_INSTRUCTION);

        $corps = $this->documentManager->genererCorps($dossier, DocumentType::TYPE_COURRIER_MINISTERE, motifRejet: $motifRejet);

        $this->assertIsString($corps);
        $crawler = new Crawler($corps);
        /** @var Crawler $paragraphs */
        $paragraphs = $crawler->filter('p');
        $this->assertEquals($nbParagraphes, $paragraphs->count());
        foreach ($elements as $element) {
            [$numeroParagraphe, $texte] = $element;
            $this->assertStringContainsString($texte, $paragraphs->eq($numeroParagraphe)->text());
        }
    }

    protected function getDossierParEtat(EtatDossierType $etat, int $index = 0): ?BrisPorte
    {
        $dossiers = $this->em->getRepository(BrisPorte::class)->listerDossierParEtat($etat);

        return @$dossiers[$index] ?? null;
    }

}