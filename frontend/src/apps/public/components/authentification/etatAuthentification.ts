export const TypePersonne = {
  Physique: "physique",
  Morale: "morale",
} as const;
export type TypePersonne = (typeof TypePersonne)[keyof typeof TypePersonne];

export type EtatAuthentification = {
  typePersonne?: TypePersonne; // uniquement pertinent si profil === Plaignant
  dejaInscrit?: boolean;
};

export type EtapeCle =
  | "personne-morale"
  | "deja-inscrit"
  | "inscription-physique"
  | "inscription-morale"
  | "connexion-physique"
  | "connexion-morale";

/**
 * Dérive, à partir de l'état courant, la liste ordonnée des étapes à afficher
 * (questions déjà répondues + la prochaine question ou le formulaire final).
 * Pas de champ "avancement" séparé à maintenir : le parcours est un arbre strict,
 * donc entièrement déductible de l'état.
 */
export function resoudreParcours(etat: EtatAuthentification): EtapeCle[] {
  const etapes: EtapeCle[] = ["personne-morale"];
  if (etat.typePersonne !== undefined) {
    etapes.push("deja-inscrit");
    if (etat.dejaInscrit !== undefined) {
      const morale = etat.typePersonne === TypePersonne.Morale;
      etapes.push(
        etat.dejaInscrit
          ? morale
            ? "connexion-morale"
            : "connexion-physique"
          : morale
            ? "inscription-morale"
            : "inscription-physique",
      );
    }
  }

  return etapes;
}

// Répondre à une question tronque les réponses en aval qui ne sont plus pertinentes.

export function repondreTypePersonne(
  etat: EtatAuthentification,
  typePersonne: TypePersonne,
): EtatAuthentification {
  return { typePersonne };
}

export function repondreDejaInscrit(
  etat: EtatAuthentification,
  dejaInscrit: boolean,
): EtatAuthentification {
  return { ...etat, dejaInscrit };
}
