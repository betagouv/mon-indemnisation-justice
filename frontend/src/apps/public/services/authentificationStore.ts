import { EtatAuthentification } from "@/apps/public/components/authentification/etatAuthentification";

const CLEF_STOCKAGE = "dys_authentification";

export function getEtatAuthentification(): EtatAuthentification | undefined {
  try {
    const brut = localStorage.getItem(CLEF_STOCKAGE);
    return brut ? JSON.parse(brut) : undefined;
  } catch {
    return undefined;
  }
}

export function sauvegarderEtatAuthentification(etat: EtatAuthentification): void {
  localStorage.setItem(CLEF_STOCKAGE, JSON.stringify(etat));
}

export function clearEtatAuthentification(): void {
  localStorage.removeItem(CLEF_STOCKAGE);
}
