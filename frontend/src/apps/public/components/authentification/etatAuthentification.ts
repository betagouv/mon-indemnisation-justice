export const Profil = {
  Plaignant: "plaignant",
  Avocat: "avocat",
} as const;
export type Profil = (typeof Profil)[keyof typeof Profil];

export const TypePersonne = {
  Physique: "physique",
  Morale: "morale",
} as const;
export type TypePersonne = (typeof TypePersonne)[keyof typeof TypePersonne];

export type EtatAuthentification = {
  profil?: Profil;
  typePersonne?: TypePersonne; // uniquement pertinent si profil === Plaignant
  dejaInscrit?: boolean;
};

export type EtapeCle =
  | "profil"
  | "personne-morale"
  | "deja-inscrit"
  | "inscription-physique"
  | "inscription-morale"
  | "inscription-avocat"
  | "connexion-physique"
  | "connexion-morale"
  | "connexion-avocat";

/**
 * Dérive, à partir de l'état courant, la liste ordonnée des étapes à afficher
 * (questions déjà répondues + la prochaine question ou le formulaire final).
 * Pas de champ "avancement" séparé à maintenir : le parcours est un arbre strict,
 * donc entièrement déductible de l'état.
 */
export function resoudreParcours(etat: EtatAuthentification): EtapeCle[] {
  const etapes: EtapeCle[] = ["profil"];

  if (etat.profil === Profil.Avocat) {
    etapes.push("deja-inscrit");
    if (etat.dejaInscrit !== undefined) {
      etapes.push(etat.dejaInscrit ? "connexion-avocat" : "inscription-avocat");
    }
    return etapes;
  }

  if (etat.profil === Profil.Plaignant) {
    etapes.push("personne-morale");
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
  }

  return etapes;
}

// Répondre à une question tronque les réponses en aval qui ne sont plus pertinentes.
export function repondreProfil(profil: Profil): EtatAuthentification {
  return { profil };
}

export function repondreTypePersonne(etat: EtatAuthentification, typePersonne: TypePersonne): EtatAuthentification {
  return { profil: etat.profil, typePersonne };
}

export function repondreDejaInscrit(etat: EtatAuthentification, dejaInscrit: boolean): EtatAuthentification {
  return { ...etat, dejaInscrit };
}
