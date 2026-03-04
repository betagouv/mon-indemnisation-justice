import { Dossier } from "@/apps/requerant/models";
import { plainToInstance } from "class-transformer";
import { assert, describe, expect, it, test } from "vitest";
import { formaterDate } from "../../../helpers";

describe("la dénormalisation d'un dossier ", () => {
  describe("de type personne physique", () => {
    it("fonctionne avec des données ok", () => {
      const dossier = plainToInstance(Dossier, {
        reference: "BRI/20260201/001",
        etatActuel: {
          etat: "DEPOSE",
          data: "2026-02-01",
          requerant: {
            id: 1,
            nom: "Raquel RANDT",
          },
        },
        dataDepot: "2026-02-01",
        requerant: {
          personne: {
            civilite: "MME",
            nom: "Randt",
            nomNaissance: "",
            prenom: "Raquel",
            courriel: "raquel.randt@courriel.fr",
            telephone: "0621436587",
          },
          adresse: {
            ligne1: "12 rue des Oliviers",
            codePostal: "44100",
            commune: "Nantes",
          },
          dateNaissance: "1979-05-17",
          paysNaissance: {
            code: "FRA",
            nom: "France",
          },
          communeNaissance: {
            id: 4,
            nom: "Bourgoin-Jallieu",
            codePostal: "38300",
            departement: "Isère",
          },
        },
        adresse: {
          ligne1: "12 rue des Oliviers",
          codePostal: "44100",
          commune: "Nantes",
        },
        rapportAuLogement: "LOC",
        dateOperation: "2026-01-28",
        description: "Porte fracturée tôt ce matin",
      });

      expect(dossier).toBeInstanceOf(Dossier);
      expect(dossier.etatActuel.etat.type).toBe("DEPOSE");
      expect(dossier.estPersonneMorale).toBe(false);
      expect(dossier.rapportAuLogement).toBe("LOC");
      expect(dossier.dateOperation).toSatisfy(
        (dateOperation: Date) => formaterDate(dateOperation) === "2026-01-28",
      );
      expect(dossier.adresse).toMatchObject({
        ligne1: "12 rue des Oliviers",
        codePostal: "44100",
        commune: "Nantes",
      });
      expect(dossier.requerant.adresse).toMatchObject({
        ligne1: "12 rue des Oliviers",
        codePostal: "44100",
        commune: "Nantes",
      });
    });
  });

  describe("de type personne morale", () => {
    it("fonctionne avec des données ok", () => {
      const dossier = plainToInstance(Dossier, {
        reference: "BRI/20260201/001",
        etatActuel: {
          etat: "DEPOSE",
          data: "2026-02-01",
          requerant: {
            id: 1,
            nom: "Raquel RANDT",
          },
        },
        dataDepot: "2026-02-01",
        requerant: {
          id: 2,
          raisonSociale: "SCI Les oiseaux",
          siren: "12345678944100",
          typePersonneMorale: "SCI",
          representantLegal: {
            civilite: "MME",
            nom: "Randt",
            nomNaissance: "",
            prenom: "Raquel",
            courriel: "raquel.randt@courriel.fr",
            telephone: "0621436587",
          },
          adresse: {
            ligne1: "12 rue des Oliviers",
            codePostal: "44100",
            commune: "Nantes",
          },
        },
        adresse: {
          ligne1: "12 rue des Oliviers",
          codePostal: "44100",
          commune: "Nantes",
        },
        rapportAuLogement: "PRO",
        dateOperation: "2026-01-28",
        description: "Porte fracturée tôt ce matin",
      });

      expect(dossier).toBeInstanceOf(Dossier);
      expect(dossier.etatActuel.etat.type).toBe("DEPOSE");
      expect(dossier.estPersonneMorale).toBe(true);
      assert("raisonSociale" in dossier.requerant);
      expect(dossier.requerant.raisonSociale).toBe("SCI Les oiseaux");
      expect(dossier.requerant.siren).toBe("12345678944100");
      expect(dossier.rapportAuLogement).toBe("PRO");
      expect(dossier.dateOperation).toSatisfy(
        (dateOperation: Date) => formaterDate(dateOperation) === "2026-01-28",
      );
      expect(dossier.adresse).toMatchObject({
        ligne1: "12 rue des Oliviers",
        codePostal: "44100",
        commune: "Nantes",
      });
      expect(dossier.requerant.adresse).toMatchObject({
        ligne1: "12 rue des Oliviers",
        codePostal: "44100",
        commune: "Nantes",
      });
    });
  });
});

test("Désérialisation du dossier requérant", () => {});
