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
        $document = (new Document())
            ->setOriginalFilename("Procès d'intervention")
            ->setType(DocumentType::TYPE_PV_FDO)
            ->setAjoutRequerant(false)
            ->setMime('application/pdf')
        ;

        $this->documentManager->enregistrerDocument($document, file_get_contents(dirname(__FILE__).'/fichiers/agent/fdo/pv_1.pdf'));
        $this->addReference('document-agent-fdo-pv-1', $document);

        $manager->persist($document);
        $manager->flush();
    }
}
