import "reflect-metadata";
import { TestEligibilite } from "@/apps/public/models/TestEligibilite";
import {
  ActionContentieuse,
  PieceProcedure,
  TypeDecision,
} from "@/apps/public/components/types";
import { plainToInstance } from "class-transformer";
import { describe, expect, it } from "vitest";

const plainEligible = {
  dateDecision: "2023-06-15",
  actionContentieuse: "non",
  typeDecision: ["jugement_premiere_instance"],
  piecesProc: ["assignation", "ecritures"],
  preuvesDiligences: true,
};

describe("TestEligibilite", () => {
  describe("dénormalisation depuis un plain object", () => {
    it("dénormalise correctement tous les champs", () => {
      const test = plainToInstance(TestEligibilite, plainEligible);

      expect(test.dateDecision).toEqual(new Date("2023-06-15"));
      expect(test.actionContentieuse).toBe(ActionContentieuse.Non);
      expect(test.typeDecision).toEqual([TypeDecision.JugementPremiereInstance]);
      expect(test.piecesProc).toEqual([PieceProcedure.Assignation, PieceProcedure.Ecritures]);
      expect(test.preuvesDiligences).toBe(true);
    });

    it("dénormalise un tableau de pièces vide", () => {
      const test = plainToInstance(TestEligibilite, { ...plainEligible, piecesProc: [] });
      expect(test.piecesProc).toEqual([]);
    });

    it("tolère les champs absents", () => {
      const test = plainToInstance(TestEligibilite, {});
      expect(test.dateDecision).toBeUndefined();
      expect(test.actionContentieuse).toBeUndefined();
      expect(test.typeDecision).toEqual([]);
      expect(test.piecesProc).toEqual([]);
      expect(test.preuvesDiligences).toBeUndefined();
    });
  });

  describe("estEligible", () => {
    it("est éligible quand tous les critères sont remplis", () => {
      const test = plainToInstance(TestEligibilite, plainEligible);
      expect(test.estEligible).toBe(true);
    });

    it("est non éligible si la date est prescrite", () => {
      const test = plainToInstance(TestEligibilite, { ...plainEligible, dateDecision: "2015-01-01" });
      expect(test.estEligible).toBe(false);
    });

    it("est non éligible si action contentieuse en cours", () => {
      const test = plainToInstance(TestEligibilite, { ...plainEligible, actionContentieuse: "oui" });
      expect(test.estEligible).toBe(false);
    });

    it("est non éligible si aucune décision de justice", () => {
      const test = plainToInstance(TestEligibilite, { ...plainEligible, typeDecision: [] });
      expect(test.estEligible).toBe(false);
    });

    it("est non éligible si aucune pièce de procédure", () => {
      const test = plainToInstance(TestEligibilite, { ...plainEligible, piecesProc: [] });
      expect(test.estEligible).toBe(false);
    });

    it("est non éligible sans preuve de diligences", () => {
      const test = plainToInstance(TestEligibilite, { ...plainEligible, preuvesDiligences: false });
      expect(test.estEligible).toBe(false);
    });
  });
});
