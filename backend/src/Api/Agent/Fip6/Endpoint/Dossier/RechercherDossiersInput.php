<?php

namespace MonIndemnisationJustice\Api\Agent\Fip6\Endpoint\Dossier;

use MonIndemnisationJustice\Api\Requerant\Dossier\Normalization\EntityResolveur;
use MonIndemnisationJustice\Entity\Agent;
use MonIndemnisationJustice\Entity\EtatDossierType;

class RechercherDossiersInput
{
    public int $p = 1;
    public int $t = 20;
    // Paramètre GET lié aux états du dossier
    public ?string $e = null;
    // Paramètre GET lié aux rédacteurs attribués
    public ?string $a = null;
    // Paramètre GET lié à la recherche textuelle
    public ?string $r = null;

    public function page(): int
    {
        return $this->p;
    }

    public function taille(): int
    {
        return $this->t;
    }

    /**
     * @return EtatDossierType[]
     */
    public function etats(): array
    {
        if (null === $this->e) {
            return [
                EtatDossierType::DOSSIER_A_ATTRIBUER,
                EtatDossierType::DOSSIER_A_INSTRUIRE,
                EtatDossierType::DOSSIER_EN_INSTRUCTION,
                EtatDossierType::DOSSIER_OK_A_SIGNER,
                EtatDossierType::DOSSIER_OK_A_APPROUVER,
                EtatDossierType::DOSSIER_OK_A_VERIFIER,
                EtatDossierType::DOSSIER_OK_A_INDEMNISER,
                EtatDossierType::DOSSIER_OK_INDEMNISE,
                EtatDossierType::DOSSIER_KO_A_SIGNER,
                EtatDossierType::DOSSIER_KO_REJETE,
            ];
        }

        return array_map(fn ($e) => EtatDossierType::fromSlug($e), self::extraireCritereRecherche($this->e));
    }

    /**
     * @return Agent[]
     */
    public function attributaires(): array
    {
        return EntityResolveur::resoudreListe(Agent::class, ['id' => array_filter(
            self::extraireCritereRecherche($this->a),
            fn ($a) => is_numeric($a)
        )]);
    }

    public function nonAttribue(): bool
    {
        return in_array('_', self::extraireCritereRecherche($this->a));
    }

    /**
     * @return string[]
     */
    public function filtres(): array
    {
        return self::extraireCritereRecherche($this->r ?? '');
    }

    public static function extraireCritereRecherche(?string $parametre = null): array
    {
        if (null === $parametre) {
            return [];
        }

        return array_filter(
            explode('|', $parametre),
            fn ($v) => !empty($v)
        );
    }
}
