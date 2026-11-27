<?php

namespace MonIndemnisationJustice\Tests\Api\Requerant\Dossier;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Api\Requerant\Dossier\DemarrerSignatureEndpoint;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\EtatDossierType;
use MonIndemnisationJustice\Entity\ReferenceBancaire;
use MonIndemnisationJustice\Entity\TypeReferenceBancaire;
use MonIndemnisationJustice\Message\ActivationSignatureRequerant;
use MonIndemnisationJustice\Service\Signature\Prestataire\Requerant\PrestataireSignatureRequerantInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;

#[CoversClass(DemarrerSignatureEndpoint::class)]
class DemarrerSignatureEndpointTest extends WebTestCase
{
    private const RIB = [
        'type' => 'RIB',
        'codeBanque' => '30004',
        'codeGuichet' => '00031',
        'numeroCompte' => '12345678901',
        'cleRib' => '43',
        'iban' => '',
        'nomBanque' => 'BNP Paribas',
        'titulaireDuCompte' => 'Monsieur Jean Dupont',
    ];

    protected KernelBrowser $client;
    protected EntityManagerInterface $em;
    protected Dossier $dossier;

    protected function setUp(): void
    {
        $this->client = self::createClient(['debug' => true]);
        // Même conteneur pour toutes les requêtes d'un test : conserve le prestataire factice et les messages émis
        $this->client->disableReboot();
        // Aucun prestataire de signature n'est configuré en test : l'activation (asynchrone) n'est pas jouée ici
        self::getContainer()->set(PrestataireSignatureRequerantInterface::class, $this->createStub(PrestataireSignatureRequerantInterface::class));
        $this->em = self::getContainer()->get(EntityManagerInterface::class);

        $this->dossier = current(array_filter(
            $this->em->getRepository(Dossier::class)->findAll(),
            fn (Dossier $dossier) => EtatDossierType::DOSSIER_OK_A_APPROUVER === $dossier->getEtatDossier()->getEtat()
                && $dossier->estEligibleSignatureElectronique()
                && $dossier->peutDemarrerSignature()
        ));
        $this->client->loginUser($this->dossier->getUsager(), 'requerant');
    }

    private function demarrer(array $donnees): void
    {
        $this->client->request(
            'POST',
            "/api/requerant/dossier/bris-de-porte/{$this->dossier->getId()}/demarrer-signature",
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode($donnees)
        );
    }

    private function dossierEnBase(): Dossier
    {
        $this->em->clear();

        return $this->em->find(Dossier::class, $this->dossier->getId());
    }

    public function testDemarrerAvecUnRib(): void
    {
        $this->demarrer(self::RIB);

        $this->assertResponseIsSuccessful();

        $dossier = $this->dossierEnBase();
        $reference = $dossier->getReferenceBancaire();
        $this->assertSame(TypeReferenceBancaire::RIB, $reference->getType());
        $this->assertSame('00031', $reference->getCodeGuichet());
        $this->assertNull($reference->getIban());
        $this->assertSame('Monsieur Jean Dupont', $reference->getTitulaireDuCompte());
        // Établissement reconnu d'après le code banque
        $this->assertSame('30004', $reference->getEtablissement()?->getCodeBanque());
        // La requête de signature conserve les coordonnées avec lesquelles elle a été émise
        $this->assertSame($reference->getId()->toRfc4122(), $dossier->getRequeteSignatureRequerant()->getReferenceBancaire()->getId()->toRfc4122());

        /** @var InMemoryTransport $transport */
        $transport = self::getContainer()->get('messenger.transport.async');
        $envoyes = array_map(fn ($enveloppe) => $enveloppe->getMessage(), $transport->getSent());
        $this->assertCount(1, $envoyes);
        $this->assertInstanceOf(ActivationSignatureRequerant::class, $envoyes[0]);
    }

    public function testDemarrerAvecUnIbanEtranger(): void
    {
        $this->demarrer([
            'type' => 'IBAN',
            'iban' => 'de89 3704 0044 0532 0130 00',
            'nomBanque' => 'Commerzbank',
            'titulaireDuCompte' => 'Monsieur Jean Dupont',
        ]);

        $this->assertResponseIsSuccessful();

        $reference = $this->dossierEnBase()->getReferenceBancaire();
        $this->assertSame(TypeReferenceBancaire::IBAN, $reference->getType());
        $this->assertSame('DE89370400440532013000', $reference->getIban());
        $this->assertNull($reference->getEtablissement());
    }

    public function testModifieLaReferenceNonUtiliseeParUneSignature(): void
    {
        $referenceExistante = ReferenceBancaire::depuisIban('FR7630006000011234567890189', 'Ancien titulaire');
        $this->em->persist($referenceExistante);
        $this->dossier->setReferenceBancaire($referenceExistante);
        $this->em->flush();
        $id = $referenceExistante->getId()->toRfc4122();

        $this->demarrer(self::RIB);

        $this->assertResponseIsSuccessful();
        $reference = $this->dossierEnBase()->getReferenceBancaire();
        $this->assertSame($id, $reference->getId()->toRfc4122());
        $this->assertSame(TypeReferenceBancaire::RIB, $reference->getType());
        $this->assertSame('Monsieur Jean Dupont', $reference->getTitulaireDuCompte());
    }

    public function testCreeUneNouvelleReferenceSiLaPrecedenteAServiAUneSignature(): void
    {
        $this->demarrer(self::RIB);
        $dossier = $this->dossierEnBase();
        $precedente = $dossier->getReferenceBancaire()->getId()->toRfc4122();
        // La signature précédente est annulée : une nouvelle peut être démarrée
        $dossier->getRequeteSignatureRequerant()->annuler();
        $this->em->flush();

        $this->demarrer([...self::RIB, 'nomBanque' => 'Ma banque']);

        $this->assertResponseIsSuccessful();
        $dossier = $this->dossierEnBase();
        $this->assertNotSame($precedente, $dossier->getReferenceBancaire()->getId()->toRfc4122());
        $this->assertSame('Ma banque', $dossier->getReferenceBancaire()->getNomBanque());
        $this->assertSame('BNP Paribas', $this->em->find(ReferenceBancaire::class, $precedente)->getNomBanque());
    }

    public function testRefuseDesCoordonneesIncompletes(): void
    {
        $this->demarrer([...self::RIB, 'codeGuichet' => '', 'titulaireDuCompte' => '']);

        $this->assertResponseStatusCodeSame(422);
        $this->assertNull($this->dossierEnBase()->getReferenceBancaire());
    }
}
