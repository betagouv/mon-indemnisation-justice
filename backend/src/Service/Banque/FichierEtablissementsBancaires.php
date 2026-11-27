<?php

namespace MonIndemnisationJustice\Service\Banque;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Lecture et écriture du référentiel versionné des établissements bancaires, au format CSV :
 *
 * - `code_banque` : code interbancaire à 5 chiffres
 * - `nom`
 * - `logo_url` : URL du logo SVG d'origine (ex : Wikimedia Commons), facultative
 * - `logo` : nom du fichier du logo téléchargé dans `public/images/logos/etablissements_bancaires/`, renseigné par
 *   la commande `mij:etablissements-bancaires:telecharger-logos`
 *
 * Le fichier par défaut, `ressources/etablissements-bancaires.csv`, est constitué à partir de la liste publique
 * https://www.meilleure-banque.com/liste-code-banque : il n'existe pas de base ouverte officielle des codes banque
 * (le fichier FIB de la Banque de France est sur abonnement).
 */
class FichierEtablissementsBancaires
{
    public const COLONNES = ['code_banque', 'nom', 'logo_url', 'logo'];

    public function __construct(
        #[Autowire('%kernel.project_dir%/ressources/etablissements-bancaires.csv')]
        public readonly string $cheminParDefaut,
    ) {
    }

    /**
     * @return array<int, array{code_banque: string, nom: string, logo_url: ?string, logo: ?string}> les lignes valides, dans l'ordre du fichier
     */
    public function lire(?string $chemin = null): array
    {
        $chemin ??= $this->cheminParDefaut;
        $source = @fopen($chemin, 'r');

        if (false === $source) {
            throw new \RuntimeException("Impossible de lire le fichier $chemin");
        }

        $lignes = [];
        $enTetes = null;

        try {
            while (($ligne = fgetcsv($source, separator: ',', escape: '')) !== false) {
                if (null === $enTetes) {
                    $enTetes = $ligne;
                    continue;
                }

                if ([null] === $ligne) {
                    continue;
                }

                $entree = array_combine($enTetes, array_pad(array_slice($ligne, 0, count($enTetes)), count($enTetes), null));
                $codeBanque = trim($entree['code_banque'] ?? '');

                if (!preg_match('/^\d{5}$/', $codeBanque)) {
                    continue;
                }

                $lignes[] = [
                    'code_banque' => $codeBanque,
                    'nom' => trim($entree['nom'] ?? ''),
                    'logo_url' => self::valeurOuNull($entree['logo_url'] ?? null),
                    'logo' => self::valeurOuNull($entree['logo'] ?? null),
                ];
            }
        } finally {
            fclose($source);
        }

        return $lignes;
    }

    /**
     * @param array<int, array{code_banque: string, nom: string, logo_url: ?string, logo: ?string}> $lignes
     */
    public function ecrire(array $lignes, ?string $chemin = null): void
    {
        $chemin ??= $this->cheminParDefaut;
        $temporaire = "$chemin.tmp";
        $destination = fopen($temporaire, 'w');

        fputcsv($destination, self::COLONNES, escape: '');
        foreach ($lignes as $ligne) {
            fputcsv($destination, array_map(fn (string $colonne) => $ligne[$colonne] ?? '', self::COLONNES), escape: '');
        }

        fclose($destination);
        rename($temporaire, $chemin);
    }

    private static function valeurOuNull(?string $valeur): ?string
    {
        $valeur = trim($valeur ?? '');

        return '' !== $valeur ? $valeur : null;
    }
}
