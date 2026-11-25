<?php

namespace MonIndemnisationJustice\Service\Signature\Prestataire\Requerant\Documenso;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\RequeteSignatureRequerant;
use MonIndemnisationJustice\Service\Signature\DocumentASigner;
use MonIndemnisationJustice\Service\Signature\Evenement\EvenementSignature;
use MonIndemnisationJustice\Service\Signature\Evenement\EvenementSignatureType;
use MonIndemnisationJustice\Service\Signature\Prestataire\Requerant\PrestataireSignatureRequerantInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;

/**
 * Signature électronique via une instance Documenso auto-hébergée, alternative locale à l'implémentation utilisée en
 * production nécessitant un accès à / depuis Internet ainsi qu'un abonnement payant.
 */
class PrestataireSignatureRequerantDocumenso implements PrestataireSignatureRequerantInterface
{
    /**
     * Zone du champ de signature sur la dernière page de la déclaration d'acceptation, en pourcentage de la page.
     */
    protected const array ZONE_SIGNATURE = ['positionX' => 10, 'positionY' => 72, 'width' => 35, 'height' => 8];

    public function __construct(
        protected readonly DocumensoClient $documensoClient,
        protected readonly EntityManagerInterface $em,
        // Secret du webhook, saisi dans Documenso sous Paramètres de l'équipe > Webhooks
        #[Autowire(env: 'default::DOCUMENSO_WEBHOOK_SECRET')]
        protected readonly ?string $secretWebhook = null,
    ) {
    }

    public function creerRequeteSignature(Dossier $dossier, DocumentASigner $documentASigner): RequeteSignatureRequerant
    {
        $requerant = $dossier->getRequerantPersonne();

        $idEnveloppe = $this->documensoClient->creerEnveloppe(
            "Acceptation de l'indemnisation {$dossier->getReference()}",
            "Déclaration d'acceptation {$dossier->getReference()}.pdf",
            $documentASigner->contenu,
            [
                'page' => $documentASigner->emplacementSignature->getNumeroPage(),
                'positionX' => (float) $documentASigner->emplacementSignature->getX(),
                'positionY' => (float) $documentASigner->emplacementSignature->getY(),
                'width' => (float) $documentASigner->emplacementSignature->getLargeur(),
                'height' => (float) $documentASigner->emplacementSignature->getHauteur(),
            ],
            "{$requerant->getPrenom()} {$requerant->getNom()}",
            $requerant->getCourriel() ?? $dossier->getUsager()->getEmail(),
            $dossier->getReference(),
        );

        $this->documensoClient->distribuerEnveloppe($idEnveloppe);

        return new RequeteSignatureRequerant()
            ->setDossier($dossier)
            ->setIdentifiantExterieur($idEnveloppe)
            // TODO générer l'URL de signature
            ->setDateCreation(new \DateTimeImmutable());
    }

    /**
     * Documenso ne signe pas le corps de la requête : le secret du webhook est transmis tel quel dans le header
     * `X-Documenso-Secret`.
     */
    public function verifierRequeteWebhook(Request $request, string $hash = ''): bool
    {
        if (empty($this->secretWebhook) || !$request->headers->has('x-documenso-secret')) {
            return false;
        }

        return hash_equals($this->secretWebhook, (string) $request->headers->get('x-documenso-secret'));
    }

    /**
     * Détecte un évènement de type `DOCUMENT_COMPLETED` et si une entité `RequeteSignatureRequerant` est trouvée et
     * n'est pas traitée, alors on la traite.
     */
    public function extraireEvenementSignatureWebhook(array $donnees): ?EvenementSignature
    {
        if ('DOCUMENT_COMPLETED' !== ($donnees['event'] ?? null) || !isset($donnees['payload']['envelopeId'])) {
            return null;
        }

        $requeteSignature = $this->em->getRepository(RequeteSignatureRequerant::class)->findOneBy(['identifiantExterieur' => (string) $donnees['payload']['envelopeId']]);

        if ($requeteSignature instanceof RequeteSignatureRequerant) {
            return new EvenementSignature($requeteSignature, EvenementSignatureType::SIGNEE);
        }

        return null;
    }

    public function telechargerDocumentSigne(RequeteSignatureRequerant $requeteSignatureRequerant): ?string
    {
        return $this->documensoClient->telechargerDocument($requeteSignatureRequerant->getIdentifiantExterieur());
    }
}
