import { describe, expect, it } from "vitest";
import {
  localiserCurseur,
  repartirSaisie,
} from "@common/composants/dsfr/champs/segments.ts";

// Les 4 champs d'un RIB : code banque, code guichet, numéro de compte, clé
const LONGUEURS = [5, 5, 11, 2];
const VIDES = ["", "", "", ""];

describe("Champs segmentés", () => {
  it("répartit une valeur collée dans le premier champ sur tous les champs", () => {
    const rib = "30006000011234567890189";

    expect(repartirSaisie(VIDES, LONGUEURS, 0, rib, rib.length)).toEqual({
      valeurs: ["30006", "00001", "12345678901", "89"],
      curseur: { index: 3, position: 2 },
    });
  });

  it("répartit une valeur collée au milieu sur les champs suivants seulement", () => {
    expect(
      repartirSaisie(["30006", "", "", ""], LONGUEURS, 1, "0000112", 7),
    ).toEqual({
      valeurs: ["30006", "00001", "12", ""],
      curseur: { index: 2, position: 2 },
    });
  });

  it("passe au champ suivant quand un champ est rempli", () => {
    expect(repartirSaisie(VIDES, LONGUEURS, 0, "30006", 5).curseur).toEqual({
      index: 1,
      position: 0,
    });
  });

  it("reste dans le champ lors d'une saisie au milieu d'un champ rempli", () => {
    // Insertion de « 9 » en 3ème position : le dernier caractère déborde sur le champ suivant
    expect(repartirSaisie(VIDES, LONGUEURS, 0, "300906", 3)).toEqual({
      valeurs: ["30090", "6", "", ""],
      curseur: { index: 0, position: 3 },
    });
  });

  it("remplace le début du champ suivant sans en perdre la fin", () => {
    expect(
      repartirSaisie(["3000", "12345", "", ""], LONGUEURS, 0, "300069", 6)
        .valeurs,
    ).toEqual(["30006", "92345", "", ""]);
  });

  it("ne dépasse pas le dernier champ", () => {
    expect(localiserCurseur(LONGUEURS, 3, 5)).toEqual({
      index: 3,
      position: 2,
    });
  });
});
