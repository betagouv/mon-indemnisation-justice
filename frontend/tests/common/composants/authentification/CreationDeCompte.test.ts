import { describe, expect, it } from "vitest";
import {
  motDePasseValide,
  REGLES_MOT_DE_PASSE_DEFAUT,
  RegleMotDePasse,
} from "@common/composants/authentification/CreationDeCompte.tsx";

describe("motDePasseValide", () => {
  it("accepte un mot de passe respectant les règles par défaut", () => {
    expect(motDePasseValide("Abcdefghij1!")).toBeUndefined();
  });

  it("refuse un mot de passe trop court", () => {
    expect(motDePasseValide("Abc1!")).toBe("Le mot de passe doit contenir au moins 12 caractères minimum");
  });

  it("refuse un mot de passe sans caractère spécial", () => {
    expect(motDePasseValide("Abcdefghij12")).toBe(
      "Le mot de passe doit contenir au moins 1 caractère spécial minimum",
    );
  });

  it("refuse un mot de passe sans chiffre", () => {
    expect(motDePasseValide("Abcdefghij!!")).toBe("Le mot de passe doit contenir au moins 1 chiffre minimum");
  });

  it("accepte des règles personnalisées (ex: parcours bris-de-porte)", () => {
    const regles: RegleMotDePasse[] = [
      { label: "8 caractères", test: (m) => m.length >= 8 },
      { label: "1 chiffre", test: (m) => /\d/.test(m) },
    ];

    expect(motDePasseValide("abcdefg1", regles)).toBeUndefined();
    expect(motDePasseValide("abcdefg", regles)).toBe("Le mot de passe doit contenir au moins 8 caractères");
  });

  it("expose trois règles par défaut", () => {
    expect(REGLES_MOT_DE_PASSE_DEFAUT).toHaveLength(3);
  });
});
