import { DossierApercu, Redacteur } from "@/common/models";
import "reflect-metadata";

import { plainToInstance } from "class-transformer";
import { expect, test } from "vitest";

test("désérialisation du dossier", () => {
  const dossier = plainToInstance(DossierApercu, {
    redacteur: {
      id: 3,
      nom: "Red ACTEUR",
    },
  });
  expect(dossier).toBeInstanceOf(DossierApercu);
  expect(dossier.redacteur).toBeInstanceOf(Redacteur);
  expect(dossier.redacteur?.nom).toBe("Red ACTEUR");
});
