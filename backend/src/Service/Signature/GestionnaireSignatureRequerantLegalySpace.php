<?php

namespace MonIndemnisationJustice\Service\Signature;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\RequeteSignatureRequerant;
use MonIndemnisationJustice\Service\Signature\LegalySpace\LegalySpaceClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Signature électronique via l'API B2B e-Sign de LegalySpace, prestataire retenu en production.
 *
 * Le parcours du signataire (lecture, consentement, code SMS, signature) est entièrement porté par l'interface de
 * LegalySpace, dont le lien est envoyé par courriel au requérant au démarrage du process.
 */
class GestionnaireSignatureRequerantLegalySpace implements GestionnaireSignatureRequerantInterface
{
    /**
     * Zone du champ de signature sur la dernière page de la déclaration d'acceptation, en points PDF (origine en bas à
     * gauche) pour une page A4 de 595 × 842 points. Équivalent de la zone utilisée pour Documenso (10 %, 72 %, 35 %,
     * 8 % depuis le haut de la page).
     */
    protected const array ZONE_SIGNATURE = ['x' => 60, 'y' => 168, 'width' => 208, 'height' => 67];

    /**
     * Indicatifs téléphoniques acceptés par LegalySpace pour l'envoi du code SMS.
     */
    protected const array INDICATIFS_ACCEPTES = ['32', '33', '34', '39', '41', '44', '49', '262', '351', '352', '376', '377', '508', '590', '594', '596', '681', '687', '689'];

    public const string EVENEMENT_TOUS_SIGNE = 'signer_all_signed';
    public const string EVENEMENT_SIGNATAIRE_A_SIGNE = 'signer_signed';
    public const string EVENEMENT_COURRIEL_INVALIDE = 'signer_invalid_mail';
    public const string EVENEMENT_PROCESS_PERIME = 'process_outdated';

    /**
     * @var string la base URI vers l'application de signature
     */
    protected readonly string $baseAppUri;

    public function __construct(
        protected readonly LegalySpaceClient $legalySpaceClient,
        protected readonly GenerateurDocumentASigner $generateurDocumentASigner,
        protected readonly EntityManagerInterface $em,
        protected readonly LoggerInterface $logger,
        protected readonly UrlGeneratorInterface $urlGenerator,
        #[Autowire(env: 'default::LEGALYSPACE_WEBHOOK_HASH')]
        protected readonly ?string $hashWebhook = null,
        #[Autowire(env: 'default::LEGALYSPACE_BASE_APP_URI')]
        ?string $appBaseUri = null,
    ) {
        $this->baseAppUri = rtrim($appBaseUri ?? 'https://esign.legalyspace.com', '/');
    }

    public function creerRequeteSignature(Dossier $dossier): RequeteSignatureRequerant
    {
        $requerant = $dossier->getRequerantPersonne();
        $declarationAcceptation = $this->generateurDocumentASigner->genererDeclarationAcceptation($dossier);
        $telephone = self::formaterTelephone($requerant->getTelephone());

        if (null === $telephone) {
            $this->logger->warning('Téléphone du requérant absent ou non reconnu : signature LegalySpace sans code SMS', [
                'dossier' => $dossier->getReference(),
            ]);
        }

        $idProcess = $this->legalySpaceClient->creerProcess(
            "Acceptation de l'indemnisation {$dossier->getReference()}",
            $dossier->getReference(),
        );

        $idDocument = $this->legalySpaceClient->deposerDocument(
            $idProcess,
            "Déclaration d'acceptation {$dossier->getReference()}.pdf",
            $declarationAcceptation->contenu,
        );

        $idSignataire = $this->legalySpaceClient->ajouterSignataire(
            $idProcess,
            $requerant->getPrenom() ?? '',
            $requerant->getNom() ?? '',
            $requerant->getCourriel() ?? $dossier->getUsager()->getEmail(),
            $telephone,
            $this->urlGenerator->generate(
                'requerant_react',
                ['extra' => "dossier/bris-de-porte/{$dossier->getId()}/consulter-la-decision"],
                UrlGeneratorInterface::ABSOLUTE_URL
            ),
        );

        $this->legalySpaceClient->positionnerSignature(
            $idProcess,
            $idDocument,
            $idSignataire,
            $declarationAcceptation->nombrePages,
            self::ZONE_SIGNATURE,
        );

        $this->legalySpaceClient->demarrerProcess($idProcess);

        return new RequeteSignatureRequerant()
            ->setDossier($dossier)
            ->setIdentifiantExterieur($idProcess)
            ->setDateCreation(new \DateTimeImmutable())
            ->setUrlSignatureExterne("{$this->baseAppUri}/process/{$idProcess}/signer/{$idSignataire}");
    }

    /**
     * LegalySpace ré-essaie un webhook en échec (8 tentatives sur une dizaine de jours) sans en indiquer le numéro de
     * tentative : on ne considère donc jamais une tentative comme la dernière.
     */
    public function estDerniereTentative(Request $request): bool
    {
        return false;
    }

    /**
     * Les webhooks LegalySpace ne portent ni signature ni secret : on vérifie le segment `$hash` de l'URL appelée.
     */
    public function verifierRequeteWebhook(Request $request, string $hash = ''): bool
    {
        if (empty($this->hashWebhook) || '' === $hash) {
            return false;
        }

        return hash_equals($this->hashWebhook, $hash);
    }

    /**
     * Sur un évènement `signer_all_signed`, et si une entité `RequeteSignatureRequerant` non signée correspond au
     * process, on confirme auprès de l'API que le process est bien terminé (les webhooks n'étant pas authentifiés),
     * puis on la marque comme signée et on la retourne.
     *
     * La livraison étant « au moins une fois », un même évènement peut être reçu plusieurs fois : le contrôle sur
     * {@see RequeteSignatureRequerant::estSignee()} assure le dédoublonnage.
     */
    public function gererWebhookEvenementSignature(array $donnees): ?RequeteSignatureRequerant
    {
        $type = $donnees['type'] ?? null;
        $idProcess = isset($donnees['processId']) ? (string) $donnees['processId'] : null;

        if (null === $idProcess) {
            return null;
        }

        if (in_array($type, [self::EVENEMENT_COURRIEL_INVALIDE, self::EVENEMENT_PROCESS_PERIME], true)) {
            $this->logger->warning("Évènement LegalySpace bloquant pour la signature du requérant : $type", [
                'process' => $idProcess,
                'reference' => $donnees['externalId'] ?? null,
                'payload' => $donnees['payload'] ?? null,
            ]);

            return null;
        }

        if (self::EVENEMENT_TOUS_SIGNE !== $type) {
            return null;
        }

        $requeteSignature = $this->em->getRepository(RequeteSignatureRequerant::class)->findOneBy(['identifiantExterieur' => $idProcess]);

        if (!$requeteSignature instanceof RequeteSignatureRequerant || $requeteSignature->estSignee()) {
            return null;
        }

        if (LegalySpaceClient::STATUT_PROCESS_TERMINE !== ($this->legalySpaceClient->recupererProcess($idProcess)['status'] ?? null)) {
            $this->logger->warning("Webhook LegalySpace `signer_all_signed` reçu pour un process qui n'est pas terminé", [
                'process' => $idProcess,
            ]);

            return null;
        }

        // TODO : récupérer le fichier PDF et traiter tout ça en asynchrone

        return $requeteSignature->setSignee();
    }

    /**
     * Formate un numéro de mobile français saisi librement au format attendu par LegalySpace (`+33 612345678`).
     *
     * Sont acceptées les saisies nationales (`06 12 34 56 78`) et internationales (`+33 6 12 34 56 78`,
     * `0033612345678`, `+33 (0)6 12 34 56 78`). Retourne `null` si le numéro est absent, étranger ou n'est pas un mobile
     * (06 ou 07) : la signature se fait alors sans code SMS.
     */
    public static function formaterTelephone(?string $telephone): ?string
    {
        // Ne conserver que les chiffres et le `+` (supprime espaces, points, tirets et parenthèses)
        $numero = preg_replace('/[^0-9+]/', '', $telephone ?? '');

        // Indicatif international en notation `00` plutôt que `+`
        if (str_starts_with($numero, '00')) {
            $numero = '+'.substr($numero, 2);
        }

        // Numéro français, national (`0` + 9 chiffres) ou international (`+33`, suivi ou non du `0` national), dont le
        // numéro national commence par 6 ou 7 (mobile)
        if (preg_match('/^(?:0|\+330?)([67]\d{8})$/', $numero, $correspondances)) {
            return "+33 {$correspondances[1]}";
        }

        return null;
    }
}
