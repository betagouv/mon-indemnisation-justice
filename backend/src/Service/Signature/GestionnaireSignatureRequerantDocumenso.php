<?php

namespace MonIndemnisationJustice\Service\Signature;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\RequeteSignatureRequerant;
use MonIndemnisationJustice\Service\Signature\Documenso\DocumensoClient;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;

/**
 * Signature électronique via une instance Documenso auto-hébergée, alternative locale à Youtrust (voir le service
 * `documenso` de `compose.yaml`).
 */
class GestionnaireSignatureRequerantDocumenso implements GestionnaireSignatureRequerantInterface
{
    /**
     * Zone du champ de signature sur la dernière page de la déclaration d'acceptation, en pourcentage de la page.
     */
    protected const array ZONE_SIGNATURE = ['positionX' => 10, 'positionY' => 72, 'width' => 35, 'height' => 8];

    public function __construct(
        protected readonly DocumensoClient $documensoClient,
        protected readonly GenerateurDocumentASigner $generateurDocumentASigner,
        protected readonly EntityManagerInterface $em,
        // Secret du webhook, saisi dans Documenso sous Paramètres de l'équipe > Webhooks
        #[Autowire(env: 'default::DOCUMENSO_WEBHOOK_SECRET')]
        protected readonly ?string $secretWebhook = null,
    ) {
    }

    public function creerRequeteSignature(Dossier $dossier): RequeteSignatureRequerant
    {
        $requerant = $dossier->getRequerantPersonne();
        $declarationAcceptation = $this->generateurDocumentASigner->genererDeclarationAcceptation($dossier);

        $idEnveloppe = $this->documensoClient->creerEnveloppe(
            "Acceptation de l'indemnisation {$dossier->getReference()}",
            "Déclaration d'acceptation {$dossier->getReference()}.pdf",
            $declarationAcceptation->contenu,
            ['page' => $declarationAcceptation->nombrePages] + self::ZONE_SIGNATURE,
            "{$requerant->getPrenom()} {$requerant->getNom()}",
            $requerant->getCourriel() ?? $dossier->getUsager()->getEmail(),
            $dossier->getReference(),
        );

        $this->documensoClient->distribuerEnveloppe($idEnveloppe);

        return new RequeteSignatureRequerant()
            ->setDossier($dossier)
            ->setIdentifiantExterieur($idEnveloppe)
            ->setDateCreation(new \DateTimeImmutable());
    }

    /**
     * Documenso ré-essaie un webhook en échec via sa file de tâches, sans en indiquer le numéro de tentative à
     * l'application : on ne considère donc jamais une tentative comme la dernière.
     */
    public function estDerniereTentative(Request $request): bool
    {
        return false;
    }

    /**
     * Documenso ne signe pas le corps de la requête : le secret du webhook est transmis tel quel dans le header
     * `X-Documenso-Secret`.
     */
    public function verifierRequeteWebhook(Request $request): bool
    {
        if (empty($this->secretWebhook) || !$request->headers->has('x-documenso-secret')) {
            return false;
        }

        return hash_equals($this->secretWebhook, (string) $request->headers->get('x-documenso-secret'));
    }

    /**
     * Détecte un évènement de type `DOCUMENT_COMPLETED` et si une entité `RequeteSignatureRequerant` est trouvée et
     * n'est pas traitée, alors on la traite et on la retourne.
     */
    public function gererWebhookEvenementSignature(array $donnees): ?RequeteSignatureRequerant
    {
        if ('DOCUMENT_COMPLETED' !== ($donnees['event'] ?? null) || !isset($donnees['payload']['envelopeId'])) {
            return null;
        }

        $requeteSignature = $this->em->getRepository(RequeteSignatureRequerant::class)->findOneBy(['identifiantExterieur' => (string) $donnees['payload']['envelopeId']]);

        if ($requeteSignature instanceof RequeteSignatureRequerant && !$requeteSignature->estSignee()) {
            $requeteSignature->setSignee();

            return $requeteSignature;
        }

        return null;
    }
}
