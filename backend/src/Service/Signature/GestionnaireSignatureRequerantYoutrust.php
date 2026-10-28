<?php

namespace MonIndemnisationJustice\Service\Signature;

use Doctrine\ORM\EntityManagerInterface;
use MonIndemnisationJustice\Entity\Dossier;
use MonIndemnisationJustice\Entity\RequeteSignatureRequerant;
use MonIndemnisationJustice\Service\Signature\Youtrust\YoutrustClient;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Request;

class GestionnaireSignatureRequerantYoutrust implements GestionnaireSignatureRequerantInterface
{
    public function __construct(
        protected readonly GenerateurDocumentASigner $generateurDocumentASigner,
        protected readonly YoutrustClient $youtrustClient,
        #[Autowire(env: 'YOUTRUST_WEBHOOK_ENCRYPTION_SECRET')]
        protected readonly ?string $secretChiffrementWebhook,
        #[Autowire(env: 'default::YOUTRUST_COURRIEL_REPLI')]
        // En cas de test, permet d'envoyer les courriels à une adresse de repli
        protected readonly ?string $adresseCourrielRepli = null,
        protected readonly EntityManagerInterface $em,
    ) {
    }

    public function creerRequeteSignature(Dossier $dossier): RequeteSignatureRequerant
    {
        // 1. Générer la déclaration d'acceptation en mode signature
        $declarationAcceptation = $this->generateurDocumentASigner->genererDeclarationAcceptation($dossier);

        // 2. Créer la requête de signature
        $requete = $this->youtrustClient->initiateSignatureRequest("Acceptation de l'indemnisation {$dossier->getReference()}");

        // 3. Téléverser le document
        $requete = $this->youtrustClient->uploadDocument($requete, $declarationAcceptation->contenu, $declarationAcceptation->nomFichier);

        // 4. Déclarer le requérant comme signataire
        $requete = $this->youtrustClient->addSigner(
            $requete,
            $dossier->getRequerantPersonne()->getPrenom(),
            $dossier->getRequerantPersonne()->getNom(),
            (!empty($this->adresseCourrielRepli) && !$this->youtrustClient->estProduction) ?
                $this->adresseCourrielRepli :
                ($dossier->getRequerantPersonne()->getCourriel() ?? $dossier->getUsager()->getEmail()),
            'fr'
        );

        // 5. Activer la requête de signature
        $this->youtrustClient->activateSignatureRequest($requete);

        return new RequeteSignatureRequerant()
            ->setDossier($dossier)
            ->setIdentifiantExterieur($requete->getId()->toString())
            ->setDateCreation(new \DateTimeImmutable());
    }

    /**
     * Voir la documentation Youtrust sur les retries https://developers.youtrust.com/docs/use-webhooks-in-your-app#handle-retries-gracefully.
     *
     * Il y a 2' entre la première tentative et la seconde, 6' entre la seconde et la 3ème, et 30' entre la 3ème et la 4ème.
     * Soit 38 minutes au total. On va donc considérer que la 4ème doit être la dernière, avant que le traitement bascule en
     * asynchrone, soit le 3ème ré-essai
     */
    public function estDerniereTentative(Request $request): bool
    {
        $tentative = $request->headers->has('x-yousign-retry') ? 1 + intval($request->headers->get('x-yousign-retry')) : 1;

        return 4 == $tentative;
    }

    /**
     * Vérifier la signature du message en comparant le hash du corps de la requête en `sha256`.
     *
     * Doc: https://developers.youtrust.com/docs/use-webhooks-in-your-app#-verifying-webhook-authenticity
     */
    public function verifierRequeteWebhook(Request $request): bool
    {
        if ($request->headers->has('x-yousign-signature-256')) {
            $signature = $request->headers->get('x-yousign-signature-256');

            return $signature == ('sha256='.hash_hmac('sha256', $request->getContent(), $this->secretChiffrementWebhook));
        }


        return false;
    }

    /**
     * Détecte un évènement de type `signature_request.done` (https://developers.youtrust.com/reference/signature_request-done)
     * et si une entité `RequeteSignatureRequerant` est trouvée et n'est pas traitée, alors on la traite et on la retourne.
     */
    public function gererWebhookEvenementSignature(array $donnees): ?RequeteSignatureRequerant
    {
        if ('signature_request.done' !== $donnees['event_name']) {
            return null;
        }

        $requeteSignature = $this->em->getRepository(RequeteSignatureRequerant::class)->findOneBy(['identifiantExterieur' => $donnees['signature_request']['id']]);

        if ($requeteSignature instanceof RequeteSignatureRequerant && !$requeteSignature->estSignee()) {
            $requeteSignature->setSignee();

            return $requeteSignature;
        }

        return null;
    }
}
