<?php

namespace MonIndemnisationJustice\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use MonIndemnisationJustice\Entity\Adresse;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\BrouillonDeclarationFDOBrisPorte;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorteErreurType;
use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\ProcedureJudiciaire;

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
        $brouillonPolice = (new BrouillonDeclarationFDOBrisPorte())
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
                    'serviceEnqueteur' => 'DPNC',
                    'telephone' => '0123456789',
                    'nomMagistrat' => null,
                ],
                'piecesJointes' => [
                    self::normaliserPieceJointe($this->getReference('document-agent-fdo-pv-1', Document::class)),
                    self::normaliserPieceJointe($this->getReference('document-agent-fdo-photo-2', Document::class)),
                ],
                'precisionsRequerant' => 'Logement vide lors de la perquisition',
                'coordonneesRequerant' => null,
            ])
        ;
        $manager->persist($brouillonPolice);

        $declarationPolice = (new DeclarationFDOBrisPorte())
            ->setEstErreur(DeclarationFDOBrisPorteErreurType::DOUTE)
            ->setDescriptionErreur("On a fracturé la porte d'accès au sous-sol de la résidence")
            ->setDateCreation(
                \DateTimeImmutable::createFromMutable(
                    (new \DateTime())->sub(
                        \DateInterval::createFromDateString('7 days')
                    )
                )
            )
            ->setDateOperation(
                \DateTimeImmutable::createFromMutable(
                    (new \DateTime())->sub(\DateInterval::createFromDateString('8 days'))
                )
            )
            ->setAgent($this->getReference('agent-policier', Agent::class))
            ->setAdresse(
                (new Adresse())
                    ->setLigne1('41 allée des Azalées')
                    ->setCodePostal('31000')
                    ->setLocalite('TOULOUSE')
            )
            ->setDateSoumission(new \DateTime())
            ->setProcedure(
                (new ProcedureJudiciaire())
                    ->setNumeroProcedure('PRO3177')
                    ->setServiceEnqueteur('DPNC')
                    ->setTelephone('0123456789')
            )
            ->setPiecesJointes([
                $this->getReference('document-agent-fdo-photo-2', Document::class),
            ])
        ;
        $manager->persist($declarationPolice);

        $brouillonGendarmerie = (new BrouillonDeclarationFDOBrisPorte())
            ->setDateCreation((new \DateTime())->sub(\DateInterval::createFromDateString('5 days')))
            ->setAgent($this->getReference('agent-gendarme', Agent::class))
            ->setDonnees([
                'estErreur' => 'DOUTE',
                'dateOperation' => (new \DateTime())->sub(\DateInterval::createFromDateString('6 days'))->format('Y-m-d'),
                'descriptionErreur' => 'Trompé de porte',
                'adresse' => [
                    'ligne1' => '127 boulevard des Fleurs',
                    'codePostal' => '69004',
                    'localite' => 'LYON',
                ],
                'procedure' => [
                    'numeroProcedure' => 'PRO3491',
                    'serviceEnqueteur' => 'GPNV',
                    'telephone' => '0234567891',
                    'nomMagistrat' => 'MARTEAU',
                    'juridictionOuParquet' => 'APPEL PARIS',
                ],
                'piecesJointes' => [
                    self::normaliserPieceJointe($this->getReference('document-agent-fdo-photo-1', Document::class)),
                    self::normaliserPieceJointe($this->getReference('document-agent-fdo-photo-3', Document::class)),
                ],
                'precisionsRequerant' => 'Logement vide lors de la perquisition',
                'coordonneesRequerant' => [
                    'civilite' => 'MME',
                    'nom' => 'RENTE',
                    'prenom' => 'Rekke',
                    'telephone' => '06 11 11 11 11',
                    'courriel' => 'rekke@courriel.fr',
                ],
            ])
        ;

        $manager->persist($brouillonGendarmerie);

        $manager->flush();
    }

    private static function normaliserPieceJointe(Document $pieceJointe): array
    {
        return [
            'id' => $pieceJointe->getId(),
            'filename' => $pieceJointe->getFilename(),
            'type' => $pieceJointe->getType()->value,
            'originalFilename' => $pieceJointe->getOriginalFilename(),
            'mime' => $pieceJointe->getMime(),
            'size' => $pieceJointe->getSize(),
            'fileHash' => $pieceJointe->getFileHash(),
            'estAjoutRequerant' => $pieceJointe->estAjoutRequerant(),
        ];
    }
}
