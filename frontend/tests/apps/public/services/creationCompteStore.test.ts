import { beforeEach, describe, expect, it } from "vitest";

// Pas d'environnement DOM dans la config vitest de ce projet (voir vitest.config.js) : on simule le
// strict minimum de l'API sessionStorage utilisée par le store.
function creerSessionStorageEnMemoire(): Storage {
  const donnees = new Map<string, string>();

  return {
    getItem: (cle: string) => donnees.get(cle) ?? null,
    setItem: (cle: string, valeur: string) => void donnees.set(cle, valeur),
    removeItem: (cle: string) => void donnees.delete(cle),
    clear: () => donnees.clear(),
    key: () => null,
    get length() {
      return donnees.size;
    },
  };
}

globalThis.sessionStorage = creerSessionStorageEnMemoire();

const { saveCreationCompte, getCreationCompte, clearCreationCompte } = await import(
  "@/apps/public/services/creationCompteStore.ts"
);

describe("creationCompteStore", () => {
  beforeEach(() => {
    clearCreationCompte();
  });

  it("ne contient rien par défaut", () => {
    expect(getCreationCompte()).toBeUndefined();
  });

  it("enregistre puis relit le profil et l'identifiant", () => {
    saveCreationCompte({ profil: "avocat", identifiant: "avocat@example.fr" });

    expect(getCreationCompte()).toEqual({ profil: "avocat", identifiant: "avocat@example.fr" });
  });

  it("efface les données enregistrées", () => {
    saveCreationCompte({ profil: "particulier", identifiant: "usager@example.fr" });

    clearCreationCompte();

    expect(getCreationCompte()).toBeUndefined();
  });

  it("ne stocke jamais le mot de passe", () => {
    saveCreationCompte({ profil: "particulier", identifiant: "usager@example.fr" });

    const brut = sessionStorage.getItem("dysfonctionnement_creation_compte") ?? "{}";

    expect(Object.keys(JSON.parse(brut)).sort()).toEqual(["identifiant", "profil"]);
  });
});
