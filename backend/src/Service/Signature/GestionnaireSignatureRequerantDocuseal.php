<?php

namespace MonIndemnisationJustice\Service\Signature;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\RequeteSignatureRequerant;
use MonIndemnisationJustice\Service\Signature\Docuseal\DocusealClient;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;

/**
 * Signature électronique via DocuSeal, dans son édition Pro (voir {@see DocusealClient}) : la déclaration d'acceptation
 * générée pour le dossier est téléversée à chaque soumission.
 */
class GestionnaireSignatureRequerantDocuseal implements GestionnaireSignatureRequerantInterface
{
    /**
     * DocuSeal ré-essaie un webhook en échec 11 fois, en doublant le délai à chaque fois (1', 2', 4', ..., 1024'). La
     * dernière tentative intervient donc 2047 minutes après la première, et l'avant-dernière 1023 minutes après.
     *
     * Voir `SendSubmissionCompletedWebhookRequestJob` dans https://github.com/docusealco/docuseal
     */
    protected const int DELAI_DERNIERE_TENTATIVE_MINUTES = 1536;

    /**
     * Tolérance sur l'horodatage de la signature du webhook, identique à celle de DocuSeal.
     */
    protected const int TOLERANCE_SIGNATURE_SECONDES = 5 * 60;

    /**
     * Zone du champ de signature sur la dernière page de la déclaration d'acceptation, en pixels (page A4 de 595 x 842).
     */
    protected const array ZONE_SIGNATURE = ['x' => 56, 'y' => 620, 'w' => 200, 'h' => 60];

    public function __construct(
        protected readonly DocusealClient $docusealClient,
        protected readonly GenerateurDocumentASigner $generateurDocumentASigner,
        protected readonly EntityManagerInterface $em,
        // Secret HMAC du webhook, visible dans DocuSeal sous Paramètres > Webhooks > Sécurité > HMAC
        #[Autowire(env: 'default::DOCUSEAL_WEBHOOK_HMAC_SECRET')]
        protected readonly ?string $secretHmacWebhook = null,
    ) {
    }

    public function creerRequeteSignature(Dossier $dossier): RequeteSignatureRequerant
    {
        $requerant = $dossier->getRequerantPersonne();
        $declarationAcceptation = $this->generateurDocumentASigner->genererDeclarationAcceptation($dossier);

        $idSoumission = $this->docusealClient->creerSoumissionDepuisPdf(
            "Acceptation de l'indemnisation {$dossier->getReference()}",
            "Déclaration d'acceptation {$dossier->getReference()}",
            $declarationAcceptation->contenu,
            self::ZONE_SIGNATURE + ['page' => $declarationAcceptation->nombrePages],
            "{$requerant->getPrenom()} {$requerant->getNom()}",
            $requerant->getCourriel() ?? $dossier->getUsager()->getEmail(),
            $dossier->getReference(),
        );

        return new RequeteSignatureRequerant()
            ->setDossier($dossier)
            ->setIdentifiantExterieur((string) $idSoumission)
            ->setDateCreation(new \DateTimeImmutable());
    }

    /**
     * DocuSeal n'indique pas le numéro de la tentative, mais le champ `timestamp` du corps de la requête correspond à
     * la date de la première tentative : on en déduit la dernière à partir du délai écoulé.
     */
    public function estDerniereTentative(Request $request): bool
    {
        $donnees = json_decode($request->getContent(), true);

        if (!is_array($donnees) || empty($donnees['timestamp'])) {
            return false;
        }

        try {
            $premiereTentative = new \DateTimeImmutable($donnees['timestamp']);
        } catch (\Exception) {
            return false;
        }

        return (time() - $premiereTentative->getTimestamp()) >= self::DELAI_DERNIERE_TENTATIVE_MINUTES * 60;
    }

    /**
     * Le header `X-Docuseal-Signature` vaut `<horodatage>.<hmac>`, où `<hmac>` est le hash `sha256` de
     * `<horodatage>.<corps de la requête>` avec le secret HMAC du webhook.
     */
    public function verifierRequeteWebhook(Request $request): bool
    {
        if (empty($this->secretHmacWebhook) || !$request->headers->has('x-docuseal-signature')) {
            return false;
        }

        $parties = explode('.', $request->headers->get('x-docuseal-signature'), 2);

        if (2 !== count($parties) || !ctype_digit($parties[0])) {
            return false;
        }

        [$horodatage, $signature] = $parties;

        if (abs(time() - (int) $horodatage) > self::TOLERANCE_SIGNATURE_SECONDES) {
            return false;
        }

        return hash_equals(hash_hmac('sha256', "{$horodatage}.{$request->getContent()}", $this->secretHmacWebhook), $signature);
    }

    /**
     * Détecte un évènement de type `submission.completed` et si une entité `RequeteSignatureRequerant` est trouvée
     * et n'est pas traitée, alors on la traite et on la retourne.
     */
    public function gererWebhookEvenementSignature(array $donnees): ?RequeteSignatureRequerant
    {
        if ('submission.completed' !== ($donnees['event_type'] ?? null) || !isset($donnees['data']['id'])) {
            return null;
        }

        $requeteSignature = $this->em->getRepository(RequeteSignatureRequerant::class)->findOneBy(['identifiantExterieur' => (string) $donnees['data']['id']]);

        if ($requeteSignature instanceof RequeteSignatureRequerant && !$requeteSignature->estSignee()) {
            $requeteSignature->setSignee();

            return $requeteSignature;
        }

        return null;
    }
}
