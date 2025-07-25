import "reflect-metadata";

import { plainToInstance } from "class-transformer";
import { expect, test } from "vitest";
import { DossierApercu, DossierDetail, Redacteur } from "@/common/models";

Redacteur.charger([{ id: 3, nom: "Red ACTEUR" }]);

test("désérialisation du dossier", () => {
  const dossier = plainToInstance(DossierApercu, {
    redacteur: 3,
  });
  expect(dossier).toBeInstanceOf(DossierApercu);
  expect(dossier.redacteur).toBeInstanceOf(Redacteur);
  expect(dossier.redacteur.nom).toBe("Red ACTEUR");
});
