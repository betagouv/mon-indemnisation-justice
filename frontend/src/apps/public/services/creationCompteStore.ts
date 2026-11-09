export type ProfilCreationCompte = "particulier" | "avocat";

export type CreationCompteEnregistree = {
  profil: ProfilCreationCompte;
  identifiant: string;
};

const KEY = "dysfonctionnement_creation_compte";

// Ne garde volontairement que l'identifiant et le profil : le mot de passe n'est jamais persisté
// (ni en sessionStorage, ni envoyé au backend à ce stade), pour éviter de stocker un secret en clair
// côté navigateur. La création effective du compte est différée à une étape ultérieure (autre ticket).
export function saveCreationCompte(valeurs: CreationCompteEnregistree): void {
  sessionStorage.setItem(KEY, JSON.stringify(valeurs));
}

export function getCreationCompte(): CreationCompteEnregistree | undefined {
  try {
    const brut = sessionStorage.getItem(KEY);
    return brut ? (JSON.parse(brut) as CreationCompteEnregistree) : undefined;
  } catch {
    return undefined;
  }
}

export function clearCreationCompte(): void {
  sessionStorage.removeItem(KEY);
}
