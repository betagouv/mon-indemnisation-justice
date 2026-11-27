<?php

namespace MonIndemnisationJustice\Api\Requerant\Dossier\Dto;

use MonIndemnisationJustice\Entity\ReferenceBancaire;
use MonIndemnisationJustice\Entity\TypeReferenceBancaire;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * Coordonnées bancaires saisies par le requérant pour démarrer la signature électronique.
 *
 * Seule la présence des champs requis par le `type` est contrôlée : la validité du format et des clés (RIB, IBAN) est
 * signalée à l'usager par le frontend sans le bloquer, puis vérifiée par un agent.
 *
 * @see frontend/src/apps/requerant/models/ReferenceBancaire.ts
 */
class ContexteSignatureElectroniqueDto
{
    #[Assert\NotNull(message: 'Le type de coordonnées bancaires (RIB ou IBAN) est requis')]
    public ?TypeReferenceBancaire $type = null;

    public ?string $codeBanque = null;
    public ?string $codeGuichet = null;
    public ?string $numeroCompte = null;
    public ?string $cleRib = null;

    public ?string $iban = null;

    #[Assert\NotBlank(message: "Le nom de l'établissement bancaire est requis")]
    #[Assert\Length(max: 255)]
    public ?string $nomBanque = null;

    #[Assert\NotBlank(message: 'Le titulaire du compte est requis')]
    #[Assert\Length(max: 255)]
    public ?string $titulaireDuCompte = null;

    #[Assert\Callback]
    public function validerChampsDuType(ExecutionContextInterface $contexte): void
    {
        if (TypeReferenceBancaire::RIB === $this->type) {
            foreach (['codeBanque' => 5, 'codeGuichet' => 5, 'numeroCompte' => 11, 'cleRib' => 2] as $champ => $longueur) {
                $valeur = ReferenceBancaire::normaliser($this->$champ ?? '');

                if ('' === $valeur || strlen($valeur) > $longueur) {
                    $contexte->buildViolation("Le champ du RIB est requis et ne peut dépasser $longueur caractères")
                        ->atPath($champ)
                        ->addViolation();
                }
            }
        }

        if (TypeReferenceBancaire::IBAN === $this->type) {
            $iban = ReferenceBancaire::normaliser($this->iban ?? '');

            if ('' === $iban || strlen($iban) > 34) {
                $contexte->buildViolation("L'IBAN est requis et ne peut dépasser 34 caractères")
                    ->atPath('iban')
                    ->addViolation();
            }
        }
    }

    /**
     * Reporte les coordonnées saisies sur la référence bancaire, créée si besoin.
     */
    public function appliquer(?ReferenceBancaire $referenceBancaire = null): ReferenceBancaire
    {
        $referenceBancaire ??= new ReferenceBancaire($this->type, trim($this->titulaireDuCompte));

        if (TypeReferenceBancaire::RIB === $this->type) {
            $referenceBancaire->definirRib($this->codeBanque, $this->codeGuichet, $this->numeroCompte, $this->cleRib);
        } else {
            $referenceBancaire->definirIban($this->iban);
        }

        return $referenceBancaire
            ->setNomBanque(trim($this->nomBanque))
            ->setTitulaireDuCompte(trim($this->titulaireDuCompte));
    }
}
