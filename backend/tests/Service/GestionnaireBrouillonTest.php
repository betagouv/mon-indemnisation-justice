<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Tests\Service;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\Brouillon;
use MonIndemnisationJustice\Entity\BrouillonType;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\DossierType;
use MonIndemnisationJustice\Entity\GeoCodePostal;
use MonIndemnisationJustice\Entity\PersonnePhysique;
use MonIndemnisationJustice\Entity\TestEligibilite;
use MonIndemnisationJustice\Entity\Usager;
use MonIndemnisationJustice\Service\GestionnaireBrouillon;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class GestionnaireBrouillonTest extends WebTestCase
{
    protected readonly EntityManagerInterface $em;
    protected readonly GestionnaireBrouillon $gestionnaireBrouillon;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();

        $this->em = $container->get(EntityManagerInterface::class);
        $this->gestionnaireBrouillon = $container->get(GestionnaireBrouillon::class);
    }

    public function testSomething(): void
    {
        $usager = $this->em->getRepository(Usager::class)->findOneBy(['email' => 'ray.keran@courriel.fr']);
        $testEligibilite = $this->em->getRepository(TestEligibilite::class)->findOneBy(['requerant' => $usager->getId()]);
        $commune = $this->em->getRepository(GeoCodePostal::class)->findOneBy(['codePostal' => '35400']);

        $brouillon = new Brouillon()
            ->setType(BrouillonType::BROUILLON_DOSSIER_BRIS_PORTE)
            ->setUsager($usager)
            ->setDonnees([
                'usager' => $usager->getId(),
                'idTestEligibilite' => $testEligibilite->id,
                'rapportAuLogement' => $testEligibilite->rapportAuLogement->value,
                'personnePhysique' => [
                    'personne' => [
                        'id' => $usager->getPersonne()->getId()->toString(),
                        'civilite' => $usager->getPersonne()->getCivilite()->value,
                        'nom' => $usager->getPersonne()->getNom(),
                        'prenom' => $usager->getPersonne()->getPrenom(),
                        'courriel' => $usager->getPersonne()->getCourriel(),
                        'telephone' => $usager->getPersonne()->getTelephone(),
                    ],
                    'adresse' => [
                        'ligne1' => '33 boulevard des Fleurs',
                        'codePostal' => '31000',
                        'commune' => 'TOULOUSE',
                    ],
                    'dateNaissance' => '1980-01-01',
                    'paysNaissance' => [
                        'code' => 'FRA',
                        'nom' => 'France',
                    ],
                    'communeNaissance' => [
                        'id' => $commune->getId(),
                        'nom' => $commune->getCommune()->getNom(),
                        'codePostal' => $commune->getCodePostal(),
                    ],
                ],
                'adresse' => [
                    'ligne1' => '33 boulevard des Fleurs',
                    'codePostal' => '31000',
                    'commune' => 'TOULOUSE',
                ],
                'description' => "Ils ont d\u00e9fonc\u00e9 ma porte",
                'estPorteBlindee' => false,
                'dateOperation' => '2026-03-06',
            ]);

        $this->em->persist($brouillon);
        $this->em->flush();

        $dossier = $this->gestionnaireBrouillon->publier($brouillon);

        $this->assertInstanceOf(Dossier::class, $dossier);
        $this->assertEquals(DossierType::BRIS_PORTE, $dossier->getType());
        $this->assertInstanceOf(PersonnePhysique::class, $dossier->getRequerantPersonnePhysique());
        $this->assertNull($dossier->getRequerantPersonneMorale());
    }
}
