import { describe, expect, it } from "vitest";
import {
  calculerCleRib,
  estCleRibValide,
  estIbanValide,
  extraireCodeBanqueIban,
  normaliserIban,
  retirerAccents,
} from "@common/services/banque.ts";

describe("Outils bancaires", () => {
  it("normalise un IBAN", () => {
    expect(normaliserIban(" fr76 3000-6000 0112.3456 7890 189 ")).toBe(
      "FR7630006000011234567890189",
    );
  });

  it("valide la clé d'un IBAN", () => {
    expect(estIbanValide("FR76 3000 6000 0112 3456 7890 189")).toBe(true);
    expect(estIbanValide("DE89 3704 0044 0532 0130 00")).toBe(true);
    // Un chiffre modifié invalide la clé
    expect(estIbanValide("FR76 3000 6000 0112 3456 7890 188")).toBe(false);
    expect(estIbanValide("FR76")).toBe(false);
  });

  it("calcule et valide la clé RIB", () => {
    // RIB contenu dans l'IBAN FR76 3000 6000 0112 3456 7890 189
    expect(calculerCleRib("30006", "00001", "12345678901")).toBe("89");
    expect(
      estCleRibValide({
        codeBanque: "30006",
        codeGuichet: "00001",
        numeroCompte: "12345678901",
        cleRib: "89",
      }),
    ).toBe(true);
    expect(
      estCleRibValide({
        codeBanque: "30006",
        codeGuichet: "00001",
        numeroCompte: "12345678901",
        cleRib: "88",
      }),
    ).toBe(false);
  });

  it("convertit les lettres d'un numéro de compte pour la clé RIB", () => {
    // A → 1, J → 1, S → 2 : ces 3 numéros sont équivalents pour le calcul de la clé
    const cle = calculerCleRib("30006", "00001", "1234567890A");
    expect(calculerCleRib("30006", "00001", "1234567890J")).toBe(cle);
    expect(calculerCleRib("30006", "00001", "12345678902")).toBe(
      calculerCleRib("30006", "00001", "1234567890S"),
    );
  });

  it("extrait le code banque d'un IBAN français uniquement", () => {
    expect(extraireCodeBanqueIban("FR76 3000 4000 03")).toBe("30004");
    expect(
      extraireCodeBanqueIban("DE89 3704 0044 0532 0130 00"),
    ).toBeUndefined();
    expect(extraireCodeBanqueIban("FR76 30")).toBeUndefined();
  });

  it("retire les accents", () => {
    expect(retirerAccents("Hélène Çédille-Noël")).toBe("Helene Cedille-Noel");
  });
});
