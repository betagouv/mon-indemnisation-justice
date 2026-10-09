<?php

namespace MonIndemnisationJustice\Service;

use MonIndemnisationJustice\Entity\DeclarationFDOBrisPorte;
use MonIndemnisationJustice\Entity\Metadonnees\NavigationRequerant;
use MonIndemnisationJustice\Entity\Personne;
use MonIndemnisationJustice\Entity\Usager;

/**
 * Construit un requérant à partir des coordonnées saisies par l'agent dans une déclaration FDO, jamais à partir
 * d'une saisie du requérant lui-même. Ne persiste rien : l'appelant décide quand valider et enregistrer.
 */
class ConstructeurUsagerDepuisDeclaration
{
    public function construire(DeclarationFDOBrisPorte $declaration): Usager
    {
        $coordonneesRequerant = $declaration->getCoordonneesRequerant();

        return new Usager()
            ->setEmail($coordonneesRequerant->getCourriel())
            ->setPersonne(
                new Personne()
                    ->setCivilite($coordonneesRequerant->getCivilite())
                    ->setPrenom($coordonneesRequerant->getPrenom())
                    ->setCourriel($coordonneesRequerant->getCourriel())
                    ->setTelephone($coordonneesRequerant->getTelephone())
                    ->setNom($coordonneesRequerant->getNom())
                    ->setNomNaissance($coordonneesRequerant->getNom())
            )
            ->setNavigation(new NavigationRequerant(idDeclaration: $declaration->getId()));
    }
}
