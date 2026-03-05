import { Dossier } from "@/apps/requerant/models";
import { instanceToPlain, plainToInstance } from "class-transformer";
import { describe, expect, it, test } from "vitest";
import { formaterDate } from "../../../helpers";

describe("la dénormalisation d'un dossier ", () => {
  describe("de type personne physique", () => {
    it("fonctionne avec des données ok", () => {
      const dossier = plainToInstance(Dossier, {
        usager: 1,
        reference: "99ed5e0c-e858-48ea-9767-be8c4423fb0d",
        etatActuel: {
          etat: "A_COMPLETER",
          date: "2026-01-28T12:34:56.789Z",
          requerant: 1,
        },
        personnePhysique: {
          personne: {
            id: "be9f1b28-11e5-4023-9ca4-2c7a56da6fb1",
            civilite: "M",
            nom: "ERRANT",
            nomNaissance: "ERRANT",
            prenom: "Rick",
            courriel: "rick.errant@courriel.fr",
          },
          adresse: {
            ligne1: "25 allée des Mimosas",
            codePostal: "44200",
            commune: "Nantes",
            ligne2: null,
          },
          dateNaissance: "1982-03-13",
          paysNaissance: {
            code: "FRA",
            nom: "France",
          },
          communeNaissance: {
            id: 33976,
            codePostal: "88300",
            nom: "Neufchateau",
            departement: "Vosges",
          },
        },
        rapportAuLogement: "BAILLEUR",
        adresse: {
          ligne1: "12 rue des Oliviers",
          codePostal: "44100",
          commune: "Nantes",
        },
        dateOperation: "2026-01-28",
        idTestEligibilite: 2132,
        estPorteBlindee: true,
        piecesJointes: [
          {
            id: 4,
            type: "attestation_information",
            chemin:
              "54b10940536c05249fde10e142bb594614a1d777c0da4d4299dfbc292b81fb2a.pdf",
            nom: "Attestation d'information d'un préjudice.pdf",
            mime: "application\/pdf",
            taille: 85276,
          },
        ],
      });

      expect(dossier).toBeInstanceOf(Dossier);
      expect(dossier.etatActuel.etat.type).toBe("A_COMPLETER");
      expect(dossier.estPersonneMorale).toBe(false);
      expect(dossier.rapportAuLogement).toBe("BAILLEUR");
      expect(dossier.dateOperation).toSatisfy(
        (dateOperation: Date) => formaterDate(dateOperation) === "2026-01-28",
      );
      expect(dossier.adresse).toMatchObject({
        ligne1: "12 rue des Oliviers",
        codePostal: "44100",
        commune: "Nantes",
      });
      expect(dossier.adresse).toMatchObject({
        ligne1: "12 rue des Oliviers",
        codePostal: "44100",
        commune: "Nantes",
      });

      const donnees = instanceToPlain(dossier);

      expect(donnees.etatActuel.etat).toBe("A_COMPLETER");
      expect(donnees.etatActuel.date).toBe("2026-01-28T12:34:56.789Z");
      expect(donnees.dateOperation).toBe("2026-01-28");
      expect(donnees.estPersonneMorale).toBeUndefined();
    });
  });

  describe("de type personne morale", () => {
    it("fonctionne avec des données ok", () => {
      const dossier = plainToInstance(Dossier, {
        usager: 1,
        reference: "99ed5e0c-e858-48ea-9767-be8c4423fb0d",
        etatActuel: {
          etat: "A_COMPLETER",
          date: "2026-01-28T12:34:56.789Z",
          requerant: 1,
        },
        personneMorale: {
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
            ligne1: "37 boulevard des Jonquilles",
            codePostal: "44300",
            commune: "Nantes",
          },
        },
        rapportAuLogement: "PROPRIETAIRE",
        adresse: {
          ligne1: "12 rue des Oliviers",
          codePostal: "44100",
          commune: "Nantes",
        },
        dateOperation: "2026-01-28",
        idTestEligibilite: 5,
        estPorteBlindee: true,
        piecesJointes: [
          {
            id: 4,
            type: "attestation_information",
            chemin:
              "54b10940536c05249fde10e142bb594614a1d777c0da4d4299dfbc292b81fb2a.pdf",
            nom: "Attestation d'information d'un préjudice.pdf",
            mime: "application\/pdf",
            taille: 85276,
          },
        ],
        description: "Porte fracturée tôt ce matin",
      });

      expect(dossier).toBeInstanceOf(Dossier);
      expect(dossier.etatActuel.etat.type).toBe("A_COMPLETER");
      expect(dossier.estPersonneMorale).toBe(true);
      expect(dossier.personneMorale).toBeDefined();
      expect(dossier.personneMorale?.raisonSociale).toBe("SCI Les oiseaux");
      expect(dossier.personneMorale?.siren).toBe("12345678944100");
      expect(dossier.rapportAuLogement).toBe("PROPRIETAIRE");
      expect(dossier.dateOperation).toSatisfy(
        (dateOperation: Date) => formaterDate(dateOperation) === "2026-01-28",
      );
      expect(dossier.adresse).toMatchObject({
        ligne1: "12 rue des Oliviers",
        codePostal: "44100",
        commune: "Nantes",
      });
      expect(dossier.personneMorale?.adresse).toMatchObject({
        ligne1: "37 boulevard des Jonquilles",
        codePostal: "44300",
        commune: "Nantes",
      });

      const donnees = instanceToPlain(dossier);

      expect(donnees.etatActuel.etat).toBe("A_COMPLETER");
      expect(donnees.etatActuel.date).toBe("2026-01-28T12:34:56.789Z");
      expect(donnees.dateOperation).toBe("2026-01-28");
      expect(donnees.estPersonneMorale).toBeUndefined();
    });
  });
});

test("Désérialisation du dossier requérant", () => {});
