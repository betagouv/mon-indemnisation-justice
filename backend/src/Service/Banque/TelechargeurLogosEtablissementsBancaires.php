<?php

namespace MonIndemnisationJustice\Service\Banque;

use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Télécharge le logo SVG d'un établissement bancaire, contrôle qu'il s'agit d'un document SVG, puis l'enregistre sous
 * un nom dérivé de son contenu (`<sha256 tronqué>.svg`) :
 * - un logo partagé par plusieurs codes banque (caisses régionales d'un même réseau) n'est stocké qu'une fois ;
 * - re-télécharger un logo inchangé ne crée pas de nouveau fichier ;
 * - un logo modifié change de nom, ce qui invalide le cache des navigateurs.
 *
 * Usage prévu en développement : les fichiers produits sont relus, versionnés, et contrôlés en CI par
 * `bin/verifier-svg` (aucun script ni référence externe) ; la production ne télécharge rien.
 */
class TelechargeurLogosEtablissementsBancaires
{
    public const DOSSIER_PUBLIC = '/images/logos/etablissements_bancaires';
    private const TAILLE_MAXIMALE = 1024 * 1024;
    // Wikimedia exige un User-Agent descriptif : https://meta.wikimedia.org/wiki/User-Agent_policy
    private const USER_AGENT = 'MonIndemnisationJustice-ReferentielBanques/1.0 (import ponctuel de logos)';

    protected readonly Filesystem $filesystem;

    public function __construct(
        protected readonly HttpClientInterface $httpClient,
        #[Autowire('%kernel.project_dir%/public'.self::DOSSIER_PUBLIC)]
        public readonly string $dossier,
    ) {
        $this->filesystem = new Filesystem();
    }

    /**
     * @return string le nom du fichier enregistré
     *
     * @throws \RuntimeException si le document n'a pu être téléchargé ou n'est pas un SVG
     */
    public function telecharger(string $url): string
    {
        $reponse = $this->httpClient->request('GET', $url, [
            'headers' => ['User-Agent' => self::USER_AGENT],
            'max_redirects' => 5,
            'timeout' => 20,
        ]);

        if (200 !== $reponse->getStatusCode()) {
            throw new \RuntimeException("Téléchargement de $url impossible (HTTP {$reponse->getStatusCode()})");
        }

        $type = $reponse->getHeaders()['content-type'][0] ?? '';
        if (!str_contains($type, 'svg') && !str_contains($type, 'xml')) {
            throw new \RuntimeException("$url n'est pas un document SVG (type $type)");
        }

        $contenu = $reponse->getContent();
        if (strlen($contenu) > self::TAILLE_MAXIMALE) {
            throw new \RuntimeException("$url dépasse la taille maximale autorisée");
        }

        if (!self::estSvg($contenu)) {
            throw new \RuntimeException("$url n'est pas un document SVG");
        }

        return $this->enregistrer($contenu);
    }

    /**
     * Document XML bien formé dont la racine est `<svg>`. Le contenu n'est pas nettoyé : il est relu avant d'être
     * versionné, puis vérifié par `bin/verifier-svg`.
     */
    public static function estSvg(string $contenu): bool
    {
        $document = new \DOMDocument();
        $precedent = libxml_use_internal_errors(true);

        try {
            // LIBXML_NONET : aucune ressource réseau ; sans LIBXML_NOENT ni LIBXML_DTDLOAD, aucune entité externe n'est chargée
            $charge = $document->loadXML($contenu, LIBXML_NONET);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($precedent);
        }

        return $charge && 'svg' === $document->documentElement?->localName;
    }

    public function enregistrer(string $svg): string
    {
        $nom = substr(hash('sha256', $svg), 0, 16).'.svg';
        $chemin = "$this->dossier/$nom";

        if (!$this->filesystem->exists($chemin)) {
            $this->filesystem->dumpFile($chemin, $svg);
        }

        return $nom;
    }

    public function existe(string $nom): bool
    {
        return $this->filesystem->exists("$this->dossier/$nom");
    }

    /**
     * @param string[] $nomsUtilises
     *
     * @return string[] les fichiers du dossier qui ne sont référencés par aucun établissement
     */
    public function listerOrphelins(array $nomsUtilises): array
    {
        if (!is_dir($this->dossier)) {
            return [];
        }

        return array_values(array_diff(
            array_map('basename', glob("$this->dossier/*.svg") ?: []),
            $nomsUtilises
        ));
    }

    public function supprimer(string $nom): void
    {
        $this->filesystem->remove("$this->dossier/$nom");
    }
}
