import { calculerPrescription } from "@/apps/public/services/prescription";
import { describe, expect, it } from "vitest";

describe("calculerPrescription", () => {
  it("retourne non rempli si la date est absente", () => {
    const result = calculerPrescription(undefined);
    expect(result.rempli).toBe(false);
    expect(result.detail).toBe("Date non renseignée");
    expect(result.expiration).toBeNull();
  });

  it("retourne non rempli si la date est invalide", () => {
    const result = calculerPrescription(new Date("pas une date"));
    expect(result.rempli).toBe(false);
    expect(result.detail).toBe("Date invalide");
    expect(result.expiration).toBeNull();
  });

  it("est prescrite si la décision date de plus de 5 ans", () => {
    const result = calculerPrescription(new Date(2010, 5, 15)); // juin 2010 → expiration 1er jan 2015
    expect(result.rempli).toBe(false);
    expect(result.expiration).toEqual(new Date(2015, 0, 1));
  });

  it("est dans les délais si la décision est récente", () => {
    const annee = new Date().getFullYear() - 1;
    const result = calculerPrescription(new Date(annee, 5, 15));
    expect(result.rempli).toBe(true);
    expect(result.expiration).toEqual(new Date(annee + 5, 0, 1));
  });

  it("l'expiration tombe toujours le 1er janvier de la 5e année suivante", () => {
    const result = calculerPrescription(new Date(2020, 11, 31)); // 31 déc 2020 → 1er jan 2025
    expect(result.expiration).toEqual(new Date(2025, 0, 1));
  });
});
