<?php

declare(strict_types=1);

namespace MonIndemnisationJustice\Tests\Controller\Requerant;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\BrisPorte;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Entity\GeoCodePostal;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * @internal
 *
 * @coversNothing
 */
class ApiPlatformTest extends WebTestCase
{
    protected KernelBrowser $client;
    protected EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = self::createClient();
        $this->em = self::getContainer()->get(EntityManagerInterface::class);
    }

    public function testPatchDossierDescriptionRequerant(): void
    {
        /** @var BrisPorte $dossier */
        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_A_FINALISER);
        $reponse = $this->patchDossier($dossier, [
            'descriptionRequerant' => 'Ils ont cassé ma porte',
        ]);

        $this->assertEquals('Ils ont cassé ma porte', $reponse->descriptionRequerant);

        $this->em->refresh($dossier);

        $this->assertEquals('Ils ont cassé ma porte', $dossier->getDescriptionRequerant());
    }

    public function testPatchDossierDateNaissance(): void
    {
        /** @var BrisPorte $dossier */
        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_A_FINALISER);
        $reponse = $this->patchDossier($dossier, [
            'requerant' => [
                'personnePhysique' => [
                    'dateNaissance' => '1990-07-26',
                ],
            ],
        ]);

        $this->assertEquals('1990-07-26', $reponse->requerant->personnePhysique->dateNaissance);

        $this->em->refresh($dossier);

        $this->assertEquals('1990-07-26', $dossier->getRequerant()->getPersonnePhysique()->getDateNaissance()->format('Y-m-d'));
    }

    public function testPatchDossierCommuneNaissance(): void
    {
        /** @var BrisPorte $dossier */
        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_A_FINALISER);

        /** @var GeoCodePostal $codePostal */
        $codePostal = $this->em->getRepository(GeoCodePostal::class)->findOneBy(['codePostal' => '44150']);

        $reponse = $this->patchDossier($dossier, [
            'requerant' => [
                'personnePhysique' => [
                    'communeNaissance' => "/api-v1/geo_code_postals/{$codePostal->getId()}",
                ],
            ],
        ]);

        $this->assertEquals("/api-v1/geo_code_postals/{$codePostal->getId()}", $reponse->requerant->personnePhysique->communeNaissance);

        $this->em->refresh($dossier);

        $this->assertEquals($codePostal->getCodePostal(), $dossier->getRequerant()->getPersonnePhysique()->getCodePostalNaissanceCode());
    }

    public function testPatchDateOperation(): void
    {
        /** @var BrisPorte $dossier */
        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_A_FINALISER);
        $hier = (new \DateTime())->modify('-1 day');

        $reponse = $this->patchDossier($dossier, [
            'dateOperationPJ' => $hier->format('Y-m-d'),
        ]);

        $this->assertEquals($hier->format('Y-m-d'), $reponse->dateOperationPJ);

        $this->em->refresh($dossier);

        $this->assertEquals($hier->format('Y-m-d'), $dossier->getDateOperationPJ()->format('Y-m-d'));
    }

    public function testPatchAdresse(): void
    {
        /** @var BrisPorte $dossier */
        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_A_FINALISER);
        $ligne1 = $dossier->getAdresse()->getLigne1();
        $ligne2 = 'Porte B2';
        $codePostal = $dossier->getAdresse()->getCodePostal();
        $localite = 'Quelque Part';

        $reponse = $this->patchDossier($dossier, [
            'adresse' => [
                'ligne2' => $ligne2,
                'localite' => $localite,
            ],
        ]);

        $this->assertEquals($ligne1, $reponse->adresse->ligne1);
        $this->assertEquals($ligne2, $reponse->adresse->ligne2);
        $this->assertEquals($codePostal, $reponse->adresse->codePostal);
        $this->assertEquals($localite, $reponse->adresse->localite);

        $this->em->refresh($dossier);

        $this->assertEquals($ligne1, $dossier->getAdresse()->getLigne1());
        $this->assertEquals($ligne2, $dossier->getAdresse()->getLigne2());
        $this->assertEquals($codePostal, $dossier->getAdresse()->getCodePostal());
        $this->assertEquals($localite, $dossier->getAdresse()->getLocalite());
    }

    public function testPatchErreurChampInconnu(): void
    {
        /** @var BrisPorte $dossier */
        $dossier = $this->getDossierParEtat(EtatDossierType::DOSSIER_A_FINALISER);

        $this->client->loginUser($dossier->getRequerant(), 'requerant');

        $this->client->request(
            'PATCH',
            "/api-v1/requerant/dossier/{$dossier->getId()}",
            server: [
                'HTTP_Content-Type' => 'application/merge-patch+json',
            ],
            content: json_encode([
                'dateDeLOperation',
            ])
        );

        $this->assertTrue($this->client->getResponse()->isClientError());
    }

    protected function patchDossier(BrisPorte $dossier, array $data): object
    {
        $this->client->loginUser($dossier->getRequerant(), 'requerant');

        $this->client->request(
            'PATCH',
            "/api-v1/requerant/dossier/{$dossier->getId()}",
            server: [
                'HTTP_Content-Type' => 'application/merge-patch+json',
            ],
            content: json_encode($data)
        );

        $this->assertTrue($this->client->getResponse()->isSuccessful());

        return json_decode($this->client->getResponse()->getContent());
    }

    protected function getDossierParEtat(EtatDossierType $etat, int $index = 0): ?BrisPorte
    {
        $dossiers = $this->em->getRepository(BrisPorte::class)->listerDossierParEtat($etat);

        return @$dossiers[$index] ?? null;
    }
}
