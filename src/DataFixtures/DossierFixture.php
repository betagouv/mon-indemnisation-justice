<?php

namespace MonIndemnisationJustice\DataFixtures;

use Doctrine\Bundle\FixturesBundle\ORMFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use MonIndemnisationJustice\Entity\Administration;
use MonIndemnisationJustice\Entity\Adresse;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\Civilite;
use MonIndemnisationJustice\Entity\GeoCodePostal;
use MonIndemnisationJustice\Entity\GeoCommune;
use MonIndemnisationJustice\Entity\GeoDepartement;
use MonIndemnisationJustice\Entity\GeoPays;
use MonIndemnisationJustice\Entity\GeoRegion;
use MonIndemnisationJustice\Entity\PersonnePhysique;
use MonIndemnisationJustice\Entity\Requerant;
use MonIndemnisationJustice\Entity\TestEligibilite;
use MonIndemnisationJustice\Service\DataGouv\ImporteurGeoPays;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class DossierFixture implements ORMFixtureInterface
{
    public function __construct(
        protected readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Pays

        $france = (new GeoPays())
            ->setCode('FRA')
            ->setCodeInsee(ImporteurGeoPays::CODE_INSEE_FRANCE)
            ->setNom('France');

        $manager->persist($france);

        $belgique = (new GeoPays())
            ->setCode('BEL')
            ->setCodeInsee('99131')
            ->setNom('Belgique');

        $manager->persist($belgique);

        $maroc = (new GeoPays())
            ->setCode('MAR')
            ->setCodeInsee('99350')
            ->setNom('Maroc');

        $manager->persist($maroc);

        // Région, département, commune et code postal
        $auvergneRhoneAlpes = (new GeoRegion())
            ->setCode('84')
            ->setNom('Auvergne-Rhône-Alpes');
        $isere = (new GeoDepartement())
            ->setRegion($auvergneRhoneAlpes)
            ->setCode('38')
            ->setNom('Isère')
            ->setDeploye(true);
        $bourgoin = (new GeoCommune())
            ->setCode('38053')
            ->setNom('Bourgoin-Jallieu')
            ->setDepartement($isere);
        $codePostal38300 = (new GeoCodePostal())
            ->setCommune($bourgoin)
            ->setCodePostal('38300');

        $manager->persist($codePostal38300);

        $paca = (new GeoRegion())
            ->setCode('93')
            ->setNom("Provence-Alpes-Côte d'Azur");
        $bouchesDuRhone = (new GeoDepartement())
            ->setRegion($paca)
            ->setCode('13')
            ->setNom('Bouches-du-Rhône')
            ->setDeploye(true);

        $manager->persist($bouchesDuRhone);

        $ileDeFrance = (new GeoRegion())
            ->setCode('11')
            ->setNom('Île-de-France');
        $seineEtMarne = (new GeoDepartement())
            ->setRegion($ileDeFrance)
            ->setCode('77')
            ->setNom('Seine-et-Marne')
            ->setDeploye(true);

        $manager->persist($seineEtMarne);

        $bretagne = (new GeoRegion())
            ->setCode('53')
            ->setNom('Bretagne');
        $illeEtVillaine = (new GeoDepartement())
            ->setRegion($bretagne)
            ->setCode('35')
            ->setNom('Ille-et-Vilaine')
            ->setDeploye(true);

        $manager->persist($illeEtVillaine);

        $paysDeLaLoire = (new GeoRegion())
            ->setCode('52')
            ->setNom('Pays de la Loire');
        $loireAtlantique = (new GeoDepartement())
            ->setRegion($paysDeLaLoire)
            ->setCode('44')
            ->setNom('Loire-Atlantique')
            ->setDeploye(false);

        $manager->persist($loireAtlantique);

        // Agents
        $redacteur = (new Agent())
            ->setNom('Acteur')
            ->setPrenom('Red')
            ->setRoles([Agent::ROLE_AGENT_REDACTEUR, Agent::ROLE_AGENT_DOSSIER, Agent::ROLE_AGENT_DOSSIER])
            ->setValide(true)
            ->setEmail('redacteur@justice.gouv.fr')
            ->setIdentifiant('c1722a03-4172-4015-9f0d-d1995d4cbe5c')
            ->setUid('1234')
            ->setAdministration(Administration::MINISTERE_JUSTICE);

        // Requérants
        $requerant = (new Requerant())
            ->setAdresse(
                (new Adresse())
                    ->setLigne1('12 rue des Oliviers')
                    ->setLocalite('Nantes')
                    ->setCodePostal('44100')
            )
            ->setPersonnePhysique(
                (new PersonnePhysique())
                    ->setCivilite(Civilite::MME)
                    ->setPrenom1('Raquel')
                    ->setNom('Randt')
                    ->setDateNaissance(new \DateTime('1979-05-17'))
                    ->setCommuneNaissance($codePostal38300)
                    ->setPaysNaissance($france)
                    ->setNumeroSecuriteSociale('')
                    ->setTelephone('0621436587')
            )
            ->setEmail('raquel.randt@courriel.fr')
            ->setVerifieCourriel(true)
            ->setRoles([Requerant::ROLE_REQUERANT]);

        $requerant->setPassword($this->passwordHasher->hashPassword($requerant, 'P4ssword'));

        // Dossiers
        $dossier = (new BrisPorte())
            ->setReference('BRI/20250101/001')
            ->setRequerant($requerant)
            ->setRedacteur($redacteur)
            ->setTestEligibilite(
                TestEligibilite::fromArray([
                    'departement' => $bouchesDuRhone,
                    'description' => 'Porte fracturée tôt ce matin',
                    'estVise' => false,
                    'estHebergeant' => false,
                    'estProprietaire' => true,
                    'aContacteAssurance' => false,
                    'requerant' => $requerant,
                    'dateSoumission' => new \DateTime('-30 seconds'),
                ])
            );

        $manager->persist($dossier);
        $manager->flush();
    }
}
