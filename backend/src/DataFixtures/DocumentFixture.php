<?php

namespace MonIndemnisationJustice\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\DocumentType;
use MonIndemnisationJustice\Service\DocumentManager;

class DocumentFixture extends Fixture
{
    public function __construct(protected readonly DocumentManager $documentManager) {}

    public function load(ObjectManager $manager): void
    {
        $pv1 = $this->creerDocument(dirname(__FILE__).'/fichiers/agent/fdo/pv_1.pdf')
            ->setType(DocumentType::TYPE_PV_FDO)
            ->setAjoutRequerant(false)
            ->setMime('application/pdf')
        ;

        $this->addReference('document-agent-fdo-pv-1', $pv1);

        $manager->persist($pv1);

        $photoFdo1 = $this->creerDocument(dirname(__FILE__).'/fichiers/agent/fdo/photo-porte-1.jpeg')
            ->setType(DocumentType::TYPE_PHOTO_FDO)
            ->setAjoutRequerant(false)
            ->setMime('image/jpeg')
        ;

        $this->addReference('document-agent-fdo-photo-1', $photoFdo1);

        $manager->persist($photoFdo1);

        $photoFdo2 = $this->creerDocument(dirname(__FILE__).'/fichiers/agent/fdo/photo-porte-2.jpg')
            ->setType(DocumentType::TYPE_PHOTO_FDO)
            ->setAjoutRequerant(false)
            ->setMime('image/jpeg')
        ;

        $this->addReference('document-agent-fdo-photo-2', $photoFdo2);

        $manager->persist($photoFdo2);

        $photoFdo3 = $this->creerDocument(dirname(__FILE__).'/fichiers/agent/fdo/photo-porte-3.png')
            ->setType(DocumentType::TYPE_PHOTO_FDO)
            ->setAjoutRequerant(false)
            ->setMime('image/png')
        ;

        $this->addReference('document-agent-fdo-photo-3', $photoFdo3);

        $manager->persist($photoFdo3);
        $manager->flush();
    }

    protected function creerDocument(string $chemin, ?string $nom = null): Document
    {
        if (!is_file($chemin)) {
            throw new \LogicException("Le fichier {$chemin} n'existe pas.");
        }

        $document = (new Document())
            ->setOriginalFilename($nom ?? basename($chemin))
        ;

        return $this->documentManager->enregistrerDocument($document, file_get_contents($chemin));
    }
}
