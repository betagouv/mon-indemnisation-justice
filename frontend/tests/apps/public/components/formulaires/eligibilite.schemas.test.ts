import { describe, expect, it } from "vitest";
import {
  SchemaEtapeActionContentieuse,
  SchemaEtapeDateDecision,
  SchemaEtapeDiligences,
  SchemaEtapePiecesProc,
  SchemaEtapeTypeDecision,
} from "@/apps/public/components/formulaires/eligibilite.schemas";

describe("SchemaEtapeDateDecision", () => {
  it("accepte une date renseignée", () => {
    expect(SchemaEtapeDateDecision.safeParse({ dateDecision: "2023-06-15" }).success).toBe(true);
  });

  it("rejette une date vide", () => {
    const result = SchemaEtapeDateDecision.safeParse({ dateDecision: "" });
    expect(result.success).toBe(false);
  });

  it("rejette l'absence de date", () => {
    const result = SchemaEtapeDateDecision.safeParse({});
    expect(result.success).toBe(false);
  });
});

describe("SchemaEtapeActionContentieuse", () => {
  it("accepte 'non'", () => {
    expect(SchemaEtapeActionContentieuse.safeParse({ actionContentieuse: "non" }).success).toBe(true);
  });

  it("accepte 'oui'", () => {
    expect(SchemaEtapeActionContentieuse.safeParse({ actionContentieuse: "oui" }).success).toBe(true);
  });

  it("rejette si non renseigné", () => {
    const result = SchemaEtapeActionContentieuse.safeParse({});
    expect(result.success).toBe(false);
    expect(result.error?.issues[0].message).toBe("Veuillez répondre à cette question");
  });

  it("rejette une valeur inconnue", () => {
    const result = SchemaEtapeActionContentieuse.safeParse({ actionContentieuse: "peut-être" });
    expect(result.success).toBe(false);
  });
});

describe("SchemaEtapeTypeDecision", () => {
  it("accepte un tableau avec une décision valide", () => {
    expect(SchemaEtapeTypeDecision.safeParse({ typeDecision: ["jugement_premiere_instance"] }).success).toBe(true);
  });

  it("accepte plusieurs décisions", () => {
    expect(
      SchemaEtapeTypeDecision.safeParse({
        typeDecision: ["jugement_premiere_instance", "arret_cour_appel"],
      }).success,
    ).toBe(true);
  });

  it("rejette un tableau vide", () => {
    const result = SchemaEtapeTypeDecision.safeParse({ typeDecision: [] });
    expect(result.success).toBe(false);
    expect(result.error?.issues[0].message).toBe("Veuillez sélectionner au moins une décision");
  });

  it("rejette si non renseigné", () => {
    const result = SchemaEtapeTypeDecision.safeParse({});
    expect(result.success).toBe(false);
  });

  it("rejette la valeur 'aucune'", () => {
    const result = SchemaEtapeTypeDecision.safeParse({ typeDecision: ["aucune"] });
    expect(result.success).toBe(false);
  });

  it("rejette une valeur inconnue", () => {
    const result = SchemaEtapeTypeDecision.safeParse({ typeDecision: ["valeur_inconnue"] });
    expect(result.success).toBe(false);
  });
});

describe("SchemaEtapePiecesProc", () => {
  it("accepte un tableau avec au moins une pièce valide", () => {
    expect(SchemaEtapePiecesProc.safeParse({ piecesProc: ["acte_introductif"] }).success).toBe(true);
  });

  it("accepte plusieurs pièces", () => {
    expect(
      SchemaEtapePiecesProc.safeParse({
        piecesProc: ["acte_introductif", "ecritures", "convocations"],
      }).success,
    ).toBe(true);
  });

  it("rejette un tableau vide", () => {
    const result = SchemaEtapePiecesProc.safeParse({ piecesProc: [] });
    expect(result.success).toBe(false);
    expect(result.error?.issues[0].message).toBe("Veuillez sélectionner au moins une pièce de procédure");
  });

  it("rejette la valeur 'aucune'", () => {
    const result = SchemaEtapePiecesProc.safeParse({ piecesProc: ["aucune"] });
    expect(result.success).toBe(false);
  });

  it("rejette une valeur inconnue dans le tableau", () => {
    const result = SchemaEtapePiecesProc.safeParse({ piecesProc: ["piece_inconnue"] });
    expect(result.success).toBe(false);
  });
});

describe("SchemaEtapeDiligences", () => {
  it("accepte true", () => {
    expect(SchemaEtapeDiligences.safeParse({ preuvesDiligences: true }).success).toBe(true);
  });

  it("accepte false", () => {
    expect(SchemaEtapeDiligences.safeParse({ preuvesDiligences: false }).success).toBe(true);
  });

  it("rejette si non renseigné", () => {
    const result = SchemaEtapeDiligences.safeParse({});
    expect(result.success).toBe(false);
    expect(result.error?.issues[0].message).toBe("Veuillez répondre à cette question");
  });
});
