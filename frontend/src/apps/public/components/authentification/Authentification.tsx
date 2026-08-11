import React, { useState } from "react";
import { FormulaireConnexion } from "./FormulaireConnexion";
import { FormulaireInscriptionAvocat } from "./FormulaireInscriptionAvocat";
import { FormulaireInscriptionUsager } from "./FormulaireInscriptionUsager";
import { FranceConnectOuEmail } from "./FranceConnectOuEmail";
import { PageActivationCompteEnvoyee } from "./PageActivationCompteEnvoyee";
import { QuestionChoix } from "./QuestionChoix";
import {
  EtatAuthentification,
  Profil,
  TypePersonne,
  repondreDejaInscrit,
  repondreProfil,
  repondreTypePersonne,
  resoudreParcours,
} from "./etatAuthentification";

export type AuthentificationProps = {
  etatInitial?: EtatAuthentification;
  onEtatChange?: (etat: EtatAuthentification) => void;
};

/**
 * Composant "questionnaire à tiroir" : révèle une question à la fois selon les réponses
 * précédentes, jusqu'à afficher le bon formulaire d'inscription ou de connexion.
 * Volontairement agnostique de la persistance (pas de localStorage ici) pour rester
 * réutilisable ailleurs avec une autre stratégie de sauvegarde.
 */
export function Authentification({ etatInitial, onEtatChange }: AuthentificationProps) {
  const [etat, setEtat] = useState<EtatAuthentification>(etatInitial ?? {});
  const [inscriptionReussie, setInscriptionReussie] = useState(false);

  const modifier = (nouvelEtat: EtatAuthentification) => {
    setEtat(nouvelEtat);
    onEtatChange?.(nouvelEtat);
  };

  if (inscriptionReussie) {
    return <PageActivationCompteEnvoyee />;
  }

  const parcours = resoudreParcours(etat);

  return (
    <div style={{ display: "flex", flexDirection: "column", gap: "2rem" }}>
      {parcours.map((etape) => {
        switch (etape) {
          case "profil":
            return (
              <QuestionChoix<Profil>
                key={etape}
                legend="Quel est votre rapport avec la procédure ?"
                valeur={etat.profil}
                options={[
                  { label: "Je suis plaignant(e)", valeur: Profil.Plaignant },
                  { label: "Je suis avocat(e)", valeur: Profil.Avocat },
                ]}
                onReponse={(profil) => modifier(repondreProfil(profil))}
              />
            );

          case "personne-morale":
            return (
              <QuestionChoix<"oui" | "non">
                key={etape}
                legend="La procédure concerne-t-elle une personne morale ?"
                valeur={etat.typePersonne === TypePersonne.Morale ? "oui" : etat.typePersonne === TypePersonne.Physique ? "non" : undefined}
                options={[
                  { label: "Oui", valeur: "oui" },
                  { label: "Non", valeur: "non" },
                ]}
                onReponse={(reponse) =>
                  modifier(repondreTypePersonne(etat, reponse === "oui" ? TypePersonne.Morale : TypePersonne.Physique))
                }
              />
            );

          case "deja-inscrit":
            return (
              <QuestionChoix<"oui" | "non">
                key={etape}
                legend="Êtes-vous déjà inscrit(e) sur la plateforme ?"
                valeur={etat.dejaInscrit === true ? "oui" : etat.dejaInscrit === false ? "non" : undefined}
                options={[
                  { label: "Oui", valeur: "oui" },
                  { label: "Non", valeur: "non" },
                ]}
                onReponse={(reponse) => modifier(repondreDejaInscrit(etat, reponse === "oui"))}
              />
            );

          case "inscription-physique":
            return (
              <FranceConnectOuEmail key={etape} action="S’inscrire avec" labelBoutonEmail="Inscription par email">
                <FormulaireInscriptionUsager typePersonne={TypePersonne.Physique} onSucces={() => setInscriptionReussie(true)} />
              </FranceConnectOuEmail>
            );

          case "inscription-morale":
            return (
              <FormulaireInscriptionUsager
                key={etape}
                typePersonne={TypePersonne.Morale}
                onSucces={() => setInscriptionReussie(true)}
              />
            );

          case "inscription-avocat":
            return <FormulaireInscriptionAvocat key={etape} onSucces={() => setInscriptionReussie(true)} />;

          case "connexion-physique":
            return (
              <FranceConnectOuEmail key={etape} action="S’identifier avec" labelBoutonEmail="Connexion par email">
                <FormulaireConnexion labelEmail="Adresse email" />
              </FranceConnectOuEmail>
            );

          case "connexion-morale":
          case "connexion-avocat":
            return <FormulaireConnexion key={etape} labelEmail="Adresse email professionnelle" />;
        }
      })}
    </div>
  );
}
