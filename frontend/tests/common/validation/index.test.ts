import { Inscription } from "@/apps/requerant/dossier/models/Inscription.ts";
import { IsEqualTo } from "@/common/validation";
import { plainToInstance } from "class-transformer";
import { validate, validateSync, ValidationOptions } from "class-validator";
import { assert, describe, expect, it } from "vitest";

describe("La contrainte IsEqualTo", () => {
  it("doit être satisfaite quand les valeurs sont égales", () => {
    class Utilisateur {
      motDePasse: string;
      @IsEqualTo("motDePasse")
      confirmation: string;
    }

    const utilisateur = plainToInstance(Utilisateur, {
      motDePasse: "P4ssword",
      confirmation: "P4ssword",
    });

    expect(validateSync(utilisateur)).toHaveLength(0);
  });
  it("doit être violée quand les valeurs sont différentes", () => {
    class Utilisateur {
      motDePasse: string;
      @IsEqualTo("motDePasse", { message: "Pas pareil" })
      confirmation: string;
    }

    const utilisateur = plainToInstance(Utilisateur, {
      motDePasse: "P4ssword",
      confirmation: "password",
    });

    const erreurs = validateSync(utilisateur);

    expect(erreurs).toHaveLength(1);
    expect(erreurs.at(0).property).toBe("confirmation");
    expect(Object.entries(erreurs.at(0).constraints)).toHaveLength(1);
    expect(erreurs.at(0).constraints.isEqualTo).toBeDefined();
    expect(erreurs.at(0).constraints.isEqualTo).toBe("Pas pareil");
  });

  it("doit être violée quand le champs cible n'existe pas", () => {
    class Utilisateur {
      motDePasse: string;
      @IsEqualTo("password", { message: "Pas pareil" })
      confirmation: string;
    }

    const utilisateur = plainToInstance(Utilisateur, {
      motDePasse: "P4ssword",
      confirmation: "password",
    });

    const erreurs = validateSync(utilisateur);

    expect(erreurs).toHaveLength(1);
    expect(erreurs.at(0).property).toBe("confirmation");
    expect(Object.entries(erreurs.at(0).constraints)).toHaveLength(1);
    expect(erreurs.at(0).constraints.isEqualTo).toBeDefined();
    expect(erreurs.at(0).constraints.isEqualTo).toBe("Pas pareil");
  });
});
