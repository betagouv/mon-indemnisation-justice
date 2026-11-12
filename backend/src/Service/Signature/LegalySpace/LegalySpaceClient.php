<?php

namespace MonIndemnisationJustice\Service\Signature\LegalySpace;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Mime\Part\DataPart;
use Symfony\Component\Mime\Part\Multipart\FormDataPart;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * Client de l'API B2B e-Sign de LegalySpace (préfixe `/v1`, spécification OpenAPI sous `/v1/openapi`).
 *
 * Un « process » regroupe les documents PDF, les signataires et les emplacements de signature. Il se construit en
 * plusieurs appels, puis est démarré, ce qui envoie l'invitation par courriel aux signataires.
 */
class LegalySpaceClient
{
    public const string STATUT_PROCESS_NOUVEAU = 'NEW';
    public const string STATUT_PROCESS_EN_SIGNATURE = 'SIGNING';
    public const string STATUT_PROCESS_TERMINE = 'DONE';
    public const string STATUT_PROCESS_ANNULE = 'CANCELED';
    public const string STATUT_PROCESS_PERIME = 'OUTDATED';

    protected readonly HttpClientInterface $httpClient;

    public function __construct(
        HttpClientInterface $httpClient,
        #[Autowire(env: 'default::LEGALYSPACE_BASE_API_URI')]
        ?string $baseUri,
        #[Autowire(env: 'default::LEGALYSPACE_API_KEY')]
        ?string $apiKey,
    ) {
        $this->httpClient = $httpClient->withOptions([
            'base_uri' => rtrim($baseUri ?? '', '/').'/',
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => "Bearer {$apiKey}",
            ],
        ]);
    }

    /**
     * Crée un process (à l'état `NEW`) et retourne son identifiant.
     *
     * Voir `POST /processes`
     *
     * @param string|null $identifiantExterne référence métier renvoyée dans chaque webhook (motif `[A-Za-z0-9+/-]+=*`)
     *
     * @throws TransportExceptionInterface
     * @throws LegalySpaceException
     */
    public function creerProcess(string $titre, ?string $identifiantExterne = null): string
    {
        return $this->extraireIdentifiantCree(
            $this->httpClient->request('POST', 'processes', [
                'json' => array_filter([
                    'title' => $titre,
                    'externalId' => $identifiantExterne,
                ]),
            ])
        );
    }

    /**
     * Dépose un document PDF dans le process et retourne son identifiant.
     *
     * Voir `POST /processes/{pid}/docs`
     *
     * @throws TransportExceptionInterface
     * @throws LegalySpaceException
     */
    public function deposerDocument(string $idProcess, string $nomFichier, string $contenuPdf): string
    {
        $formData = new FormDataPart([
            'file' => new DataPart($contenuPdf, $nomFichier, 'application/pdf'),
        ]);

        return $this->extraireIdentifiantCree(
            $this->httpClient->request('POST', "processes/{$idProcess}/docs", [
                'headers' => $formData->getPreparedHeaders()->toArray(),
                'body' => $formData->bodyToIterable(),
            ])
        );
    }

    /**
     * Déclare un signataire dans le process et retourne son identifiant.
     *
     * Le téléphone mobile, au format `+33 612345678` (indicatif, espace, numéro national), est requis pour l'envoi du
     * code SMS, sauf si `$sansSms` est vrai.
     *
     * Voir `POST /processes/{pid}/signers`
     *
     * @throws TransportExceptionInterface
     * @throws LegalySpaceException
     */
    public function ajouterSignataire(
        string $idProcess,
        string $prenom,
        string $nom,
        string $courriel,
        ?string $telephone,
    ): string {
        return $this->extraireIdentifiantCree(
            $this->httpClient->request('POST', "processes/{$idProcess}/signers", [
                'json' => array_filter([
                    'firstName' => $prenom,
                    'lastName' => $nom,
                    'email' => $courriel,
                    'phone' => null,
                    'withoutSms' => true,
                ], fn ($valeur) => null !== $valeur),
            ])
        );
    }

    /**
     * Positionne l'emplacement de la signature de `$idSignataire` sur le document `$idDocument`.
     *
     * Les coordonnées sont exprimées en points PDF (1/72 de pouce), l'origine étant en bas à gauche de la page.
     *
     * Voir `POST /processes/{pid}/signatures`
     *
     * @param array{x: int|float, y: int|float, width: int|float, height: int|float} $coordonnees
     *
     * @throws TransportExceptionInterface
     * @throws LegalySpaceException
     */
    public function positionnerSignature(string $idProcess, string $idDocument, string $idSignataire, int $page, array $coordonnees): void
    {
        $this->extraireDonneesReponse(
            $this->httpClient->request('POST', "processes/{$idProcess}/signatures", [
                'json' => [
                    'docId' => $idDocument,
                    'signerId' => $idSignataire,
                    'page' => $page,
                    'coordinates' => $coordonnees,
                ],
            ])
        );
    }

    /**
     * Démarre le process (passage à l'état `SIGNING`) : l'invitation est envoyée par courriel aux signataires.
     *
     * Voir `POST /processes/{pid}/start`
     *
     * @throws TransportExceptionInterface
     * @throws LegalySpaceException
     */
    public function demarrerProcess(string $idProcess): void
    {
        $this->extraireDonneesReponse($this->httpClient->request('POST', "processes/{$idProcess}/start"));
    }

    /**
     * Retourne le détail du process : `status`, ainsi que les listes `docs`, `signers` et `signatures`.
     *
     * Voir `GET /processes/{pid}`
     *
     * @throws TransportExceptionInterface
     * @throws LegalySpaceException
     */
    public function recupererProcess(string $idProcess): array
    {
        return $this->extraireDonneesReponse($this->httpClient->request('GET', "processes/{$idProcess}"));
    }

    /**
     * Annule un process démarré mais non terminé.
     *
     * Voir `DELETE /processes/{pid}`
     *
     * @throws TransportExceptionInterface
     * @throws LegalySpaceException
     */
    public function annulerProcess(string $idProcess): void
    {
        $this->extraireDonneesReponse($this->httpClient->request('DELETE', "processes/{$idProcess}"));
    }

    /**
     * Retourne le contenu PDF de la dernière version du document (signée si le process est terminé).
     *
     * Voir `GET /processes/{pid}/docs/{did}/download`
     *
     * @throws TransportExceptionInterface
     * @throws LegalySpaceException
     */
    public function telechargerDocument(string $idProcess, string $idDocument): string
    {
        return $this->extraireContenuBinaire($this->httpClient->request('GET', "processes/{$idProcess}/docs/{$idDocument}/download"));
    }

    /**
     * Retourne le contenu PDF du dossier de preuve (audit trail) du document signé.
     *
     * Voir `GET /processes/{pid}/docs/{did}/download-audit`
     *
     * @throws TransportExceptionInterface
     * @throws LegalySpaceException
     */
    public function telechargerDossierPreuve(string $idProcess, string $idDocument): string
    {
        return $this->extraireContenuBinaire($this->httpClient->request('GET', "processes/{$idProcess}/docs/{$idDocument}/download-audit"));
    }

    /**
     * Les créations répondent `201` avec l'URL de la ressource créée dans l'en-tête `Location`, dont le dernier segment
     * est l'identifiant. À défaut, on se rabat sur le champ `id` du corps de la réponse.
     *
     * @throws LegalySpaceException
     */
    protected function extraireIdentifiantCree(ResponseInterface $response): string
    {
        $data = $this->extraireDonneesReponse($response);
        $location = $response->getHeaders(false)['location'][0] ?? null;

        if (null !== $location && '' !== ($id = basename(rtrim(parse_url($location, PHP_URL_PATH) ?: '', '/')))) {
            return $id;
        }

        if (isset($data['id'])) {
            return (string) $data['id'];
        }

        // TODO extraire `externalLink`


        throw new LegalySpaceException("Réponse inattendue de l'API LegalySpace : identifiant de la ressource créée introuvable.");
    }

    /**
     * @throws LegalySpaceException
     */
    protected function extraireDonneesReponse(ResponseInterface $response): array
    {
        $contenu = $response->getContent(false);
        $data = json_decode($contenu ?: '{}', true);

        if ($response->getStatusCode() >= 400) {
            throw LegalySpaceException::depuisReponse($response->getStatusCode(), is_array($data) ? $data : []);
        }

        return is_array($data) ? $data : [];
    }

    /**
     * @throws LegalySpaceException
     */
    protected function extraireContenuBinaire(ResponseInterface $response): string
    {
        if ($response->getStatusCode() >= 400) {
            $data = json_decode($response->getContent(false) ?: '{}', true);

            throw LegalySpaceException::depuisReponse($response->getStatusCode(), is_array($data) ? $data : []);
        }

        return $response->getContent();
    }
}
