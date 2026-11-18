<?php

namespace MonIndemnisationJustice\Service\Signature\Prestataire\Requerant\LegalySpace;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\RequeteSignatureRequerant;
use MonIndemnisationJustice\Service\Signature\DocumentASigner;
use MonIndemnisationJustice\Service\Signature\Evenement\EvenementSignature;
use MonIndemnisationJustice\Service\Signature\Evenement\EvenementSignatureType;
use MonIndemnisationJustice\Service\Signature\Prestataire\Requerant\PrestataireSignatureRequerantInterface;
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
class PrestataireSignatureLegalySpace implements PrestataireSignatureRequerantInterface
{
    public const string EVENEMENT_TOUS_SIGNE = 'signer_all_signed';
    public const string EVENEMENT_COURRIEL_INVALIDE = 'signer_invalid_mail';
    public const string EVENEMENT_PROCESS_PERIME = 'process_outdated';

    /**
     * @var string la base URI vers l'application de signature
     */
    protected readonly string $baseAppUri;

    public function __construct(
        protected readonly LegalySpaceClient $legalySpaceClient,
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

    public function creerRequeteSignature(Dossier $dossier, DocumentASigner $documentASigner): RequeteSignatureRequerant
    {
        $requerant = $dossier->getRequerantPersonne();
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
            $documentASigner->contenu,
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
            $documentASigner->emplacementSignature->numeroPage,
            [
                'x' => $documentASigner->emplacementSignature->x,
                'y' => $documentASigner->emplacementSignature->y - ($documentASigner->emplacementSignature->hauteur / 2),
                'width' => $documentASigner->emplacementSignature->largeur,
                'height' => $documentASigner->emplacementSignature->hauteur,
            ],
        );

        $this->legalySpaceClient->demarrerProcess($idProcess);

        return new RequeteSignatureRequerant()
            ->setDossier($dossier)
            ->setIdentifiantExterieur($idProcess)
            ->setDateCreation(new \DateTimeImmutable())
            ->setUrlSignatureExterne("{$this->baseAppUri}/process/{$idProcess}/signer/{$idSignataire}");
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
    public function extraireEvenementSignatureWebhook(array $donnees): ?EvenementSignature
    {
        $type = $donnees['type'] ?? null;
        $idProcess = isset($donnees['processId']) ? (string) $donnees['processId'] : null;


        if (null === $idProcess) {
            return null;
        }

        if (!in_array($type, [self::EVENEMENT_COURRIEL_INVALIDE, self::EVENEMENT_PROCESS_PERIME, self::EVENEMENT_TOUS_SIGNE], true)) {
            $this->logger->warning("Évènement LegalySpace ignoré : $type", $donnees);

            return null;
        }

        $requeteSignature = $this->em->getRepository(RequeteSignatureRequerant::class)->findOneBy(['identifiantExterieur' => $idProcess]);

        if (!$requeteSignature instanceof RequeteSignatureRequerant) {
            return null;
        }

        if (self::EVENEMENT_COURRIEL_INVALIDE === $type) {
            return new EvenementSignature($requeteSignature, EvenementSignatureType::ECHOUEE, erreur: 'Adresse email invalide');
        }

        if (self::EVENEMENT_PROCESS_PERIME === $type) {
            return new EvenementSignature($requeteSignature, EvenementSignatureType::EXPIREE);
        }

        return new EvenementSignature($requeteSignature, EvenementSignatureType::SIGNEE);
    }

    public function telechargerDocumentSigne(RequeteSignatureRequerant $requeteSignatureRequerant): ?string
    {
        return $this->legalySpaceClient->telechargerDocument($requeteSignatureRequerant->getIdentifiantExterieur());
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
