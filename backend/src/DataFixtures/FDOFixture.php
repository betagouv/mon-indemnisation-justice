<?php

namespace MonIndemnisationJustice\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use MonIndemnisationJustice\DataFixtures\Helpers\MailpitManager;
use MonIndemnisationJustice\Entity\Administration;
use MonIndemnisationJustice\Entity\Adresse;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\Civilite;
use MonIndemnisationJustice\Entity\CoordonneesRequerant;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorteErreurType;
use MonIndemnisationJustice\Entity\Document;
use MonIndemnisationJustice\Entity\FDO\EtablissementFDO;
use MonIndemnisationJustice\Entity\GeoCodePostal;
use MonIndemnisationJustice\Entity\ProcedureJudiciaire;

class FDOFixture extends Fixture implements DependentFixtureInterface
{
    public function __construct(protected readonly MailpitManager $mailpitManager)
    {
    }

    public function getDependencies(): array
    {
        return [
            AgentFixture::class,
            DocumentFixture::class,
            AdministrationFixture::class,
            GeoFixture::class,
        ];
    }

    public function load(ObjectManager $manager): void
    {
        // Suppression des emails
        $this->mailpitManager->supprimerMessages();

        // Établissements
        $commissariatProvins = new EtablissementFDO()
            ->setNom(' Commissariat de police de Provins')
            ->setIdentifiant('')
            ->setCodePostal($this->getReference('code-postal-77160', GeoCodePostal::class))
            ->setAdministration($this->getReference('administration-PN', Administration::class));

        $manager->persist($commissariatProvins);

        $commissariatParis20 = new EtablissementFDO()
            ->setNom('Commissariat de police de Paris 20ème arrondissement')
            ->setIdentifiant('')
            ->setCodePostal($this->getReference('code-postal-75020', GeoCodePostal::class))
            ->setAdministration($this->getReference('administration-PP', Administration::class));

        $manager->persist($commissariatParis20);

        $gendarmerieAuray = new EtablissementFDO()
            ->setNom("Gendarmerie - Brigade d'Auray")
            ->setIdentifiant('1006429')
            ->setCodePostal($this->getReference('code-postal-56400', GeoCodePostal::class))
            ->setAdministration($this->getReference('administration-GN', Administration::class));

        $manager->persist($gendarmerieAuray);

        // Affectations
        $policier = $this->getReference('agent-policier', Agent::class);

        $policier->affecter($commissariatProvins);
        $manager->persist($policier);

        $gendarme = $this->getReference('agent-gendarme', Agent::class);

        $gendarme->affecter($gendarmerieAuray);
        $manager->persist($gendarme);

        // Déclarations de bris de porte
        $brouillonPolice = new DeclarationFDOBrisPorte()
            ->setAgent($this->getReference('agent-policier', Agent::class))
            ->setEstErreur(DeclarationFDOBrisPorteErreurType::DOUTE)
            ->setDateOperation(new \DateTimeImmutable('today')->sub(\DateInterval::createFromDateString('3 days')))
            ->setDescriptionErreur('Cassé la porte')
            ->setAdresse(
                new Adresse()
                    ->setLigne1('127 boulevard des Fleurs')
                    ->setLigne2('Porte B')
                    ->setCodePostal('75021')
                    ->setLocalite('PARIS')
            )
            ->setProcedure(
                new ProcedureJudiciaire()
                    ->setNumeroProcedure('PRO1653')
                    ->setServiceEnqueteur('DPNC')
                    ->setTelephone('0123456789')
            )
            ->setPiecesJointes([
                $this->getReference('document-agent-fdo-pv-1', Document::class),
                $this->getReference('document-agent-fdo-photo-2', Document::class),
            ])
            ->setPrecisionsRequerant('Logement vide lors de la perquisition');
        $manager->persist($brouillonPolice);

        $declarationPolice = new DeclarationFDOBrisPorte()
            ->setEstErreur(DeclarationFDOBrisPorteErreurType::DOUTE)
            ->setDescriptionErreur("On a fracturé la porte d'accès au sous-sol de la résidence")
            ->setDateCreation(
                new \DateTimeImmutable()->sub(
                    \DateInterval::createFromDateString('7 days')
                )
            )
            ->setDateOperation(
                new \DateTimeImmutable()->sub(\DateInterval::createFromDateString('8 days'))
            )
            ->setAgent($this->getReference('agent-policier', Agent::class))
            ->setAdresse(
                new Adresse()
                    ->setLigne1('41 allée des Azalées')
                    ->setCodePostal('31000')
                    ->setLocalite('TOULOUSE')
            )
            ->setDateSoumission(new \DateTimeImmutable())
            ->setProcedure(
                new ProcedureJudiciaire()
                    ->setNumeroProcedure('PRO3177')
                    ->setServiceEnqueteur('DPNC')
                    ->setTelephone('0123456789')
            )
            ->setPiecesJointes([
                $this->getReference('document-agent-fdo-photo-2', Document::class),
            ]);
        $manager->persist($declarationPolice);

        $brouillonGendarmerie = new DeclarationFDOBrisPorte()
            ->setDateCreation(new \DateTimeImmutable()->sub(\DateInterval::createFromDateString('5 days')))
            ->setAgent($this->getReference('agent-gendarme', Agent::class))
            ->setEstErreur(DeclarationFDOBrisPorteErreurType::DOUTE)
            ->setDateOperation(new \DateTimeImmutable('today')->sub(\DateInterval::createFromDateString('6 days')))
            ->setDescriptionErreur('Trompé de porte')
            ->setAdresse(
                new Adresse()
                    ->setLigne1('127 boulevard des Fleurs')
                    ->setCodePostal('69004')
                    ->setLocalite('LYON')
            )
            ->setProcedure(
                new ProcedureJudiciaire()
                    ->setNumeroProcedure('PRO3491')
                    ->setServiceEnqueteur('GPNV')
                    ->setTelephone('0234567891')
                    ->setNomMagistrat('MARTEAU')
                    ->setJuridictionOuParquet('APPEL PARIS')
            )
            ->setPiecesJointes([
                $this->getReference('document-agent-fdo-photo-1', Document::class),
                $this->getReference('document-agent-fdo-photo-3', Document::class),
            ])
            ->setPrecisionsRequerant('Logement vide lors de la perquisition')
            ->setCoordonneesRequerant(
                new CoordonneesRequerant()
                    ->setCivilite(Civilite::MME)
                    ->setNom('RENTE')
                    ->setPrenom('Rekke')
                    ->setTelephone('06 11 11 11 11')
                    ->setCourriel('rekke@courriel.fr')
            );

        $manager->persist($brouillonGendarmerie);

        $declarationGendarmerie = new DeclarationFDOBrisPorte()
            ->setEstErreur(DeclarationFDOBrisPorteErreurType::OUI)
            ->setDescriptionErreur('Intervention à la mauvaise porte')
            ->setDateCreation(
                new \DateTimeImmutable()->sub(
                    \DateInterval::createFromDateString('1 day')
                )
            )
            ->setDateOperation(
                new \DateTimeImmutable()->sub(\DateInterval::createFromDateString('1 day'))
            )
            ->setAgent($this->getReference('agent-gendarme', Agent::class))
            ->setAdresse(
                new Adresse()
                    ->setLigne1('19 rue de la Gare')
                    ->setCodePostal('75019')
                    ->setLocalite('PARIS')
            )
            ->setDateSoumission(new \DateTimeImmutable())
            ->setProcedure(
                new ProcedureJudiciaire()
                    ->setNumeroProcedure('PRO871552')
                    ->setServiceEnqueteur('GNFD')
                    ->setTelephone('0123456789')
            )
            ->setCoordonneesRequerant(
                new CoordonneesRequerant()
                    ->setNom('LEMÉE')
                    ->setPrenom('Pierre')
                    ->setCivilite(Civilite::M)
                    ->setCourriel('pierre.lemee@3f-habitat.fr')
                    ->setTelephone('0123456789')
            );
        $manager->persist($declarationGendarmerie);

        $manager->flush();
    }
}
