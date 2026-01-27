<?php

namespace MonIndemnisationJustice\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\BrouillonDeclarationFDOBrisPorte;
use MonIndemnisationJustice\Entity\Document;

class FDOFixture extends Fixture implements DependentFixtureInterface
{
    public function getDependencies(): array
    {
        return [
            AgentFixture::class,
            DocumentFixture::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $pieceJointe = $this->getReference('document-agent-fdo-pv-1', Document::class);
        $brouillon = (new BrouillonDeclarationFDOBrisPorte())
            ->setDateCreation(new \DateTime())
            ->setAgent($this->getReference('agent-policier', Agent::class))
            ->setDonnees([
                'estErreur' => 'DOUTE',
                'dateOperation' => (new \DateTime())->sub(\DateInterval::createFromDateString('3 days'))->format('Y-m-d'),
                'descriptionErreur' => 'Cassé la porte',
                'adresse' => [
                    'ligne1' => '127 boulevard des Fleurs',
                    'ligne2' => 'Porte B',
                    'codePostal' => '75021',
                    'localite' => 'PARIS',
                ],
                'procedure' => [
                    'numeroProcedure' => 'PRO1653',
                    'serviceEnqueteur' => 'GPNV',
                    'telephone' => '0123456789',
                    'nomMagistrat' => null,
                ],
                'piecesJointes' => [
                    (object) [
                        'id' => $pieceJointe->getId(),
                        'filename' => $pieceJointe->getFilename(),
                        'type' => $pieceJointe->getType()->value,
                        'originalFilename' => $pieceJointe->getOriginalFilename(),
                        'mime' => $pieceJointe->getMime(),
                        'size' => $pieceJointe->getSize(),
                        'estAjoutRequerant' => $pieceJointe->estAjoutRequerant(),
                    ],
                ],
                'precisionsRequerant' => 'Logement vide lors de la perquisition',
                'coordonneesRequerant' => null,
            ])
        ;
        $manager->persist($brouillon);
        $manager->flush();
    }

    protected function getCacheKey(): string
    {
        return 'fixture-agent';
    }
}
