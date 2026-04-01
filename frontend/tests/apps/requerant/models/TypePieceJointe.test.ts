import { PieceJointeType, TypePieceJointe } from "@/apps/requerant/models/TypePieceJointe";
import { describe, expect, it } from "vitest";

describe("libeller un type de pièce jointe ", () => {
  describe("en titre long ", () => {
    it("fonctionne", () => {
      const libelles: { [key in PieceJointeType]?: string } = {
        attestation_information:
          "Attestation complétée par les forces de l'ordre",
        photo_prejudice: "Photo de la porte endommagée",
        carte_identite: "Pièce d'identité",
        facture: "Facture",
        rib: "RIB",
        titre_propriete: "Titre de propriété",
        contrat_location: "Contrat de location",
        non_prise_en_charge_bailleur:
          "Attestation de non prise en charge par le bailleur",
        non_prise_en_charge_assurance:
          "Attestation de non prise en charge par l'assurance",
        piece_identite_signataire: "Pièce d'identité du signataire",
        extrait_kbis: "Extrait Kbis",
        quittance_subrogative: "Quittance subrogative",
        pouvoir_signataire: "Justificatif des pouvoirs du signataire",
        pv_association_statuts: "Procès-verbal désignant le représentant légal",
        pv_ag_syndic: "Procès-verbal d’assemblée générale désignant le syndic",
        declaration_rna_joafe: "Récépissé de déclaration RNA",
        identification_etablissement_publique:
          "Document identifiant la collectivité",
      };

      Object.entries(libelles).forEach(
        ([type, libelle]: [PieceJointeType, string]) => {
          expect(
            TypePieceJointe.depuis(type).libelle({
              court: true,
              titre: true,
              enCapitales: true,
            }),
          ).toBe(libelle);
        },
      );
    });
  });
  describe("en nom pluriel indéfini cours ", () => {
    it("fonctionne", () => {
      const libelles: { [key in PieceJointeType]?: string } = {
        attestation_information:
          "des attestations complétées par les forces de l'ordre",
        photo_prejudice: "des photos de la porte endommagée",
        carte_identite: "des copies de votre pièce d'identité recto-verso",
        facture:
          "des factures acquittées attestant de la réalité des travaux de remise en état à l'identique",
        rib: "des relevés d'identité bancaire",
        extrait_kbis: "des extraits Kbis datant de moins de 3 mois",
        quittance_subrogative:
          "des quittances subrogatives ou tout document établissant la subrogation",
      };

      Object.entries(libelles).forEach(
        ([type, libelle]: [PieceJointeType, string]) => {
          expect(
            TypePieceJointe.depuis(type).libelle({
              court: false,
              titre: false,
              pluriel: true,
              defini: false,
              enCapitales: false,
            }),
          ).toBe(libelle);
        },
      );
    });
  });
  describe("en nom singulier défini cours ", () => {
    it("fonctionne", () => {
      const libelles: { [key in PieceJointeType]?: string } = {
        attestation_information:
          "des attestations complétées par les forces de l'ordre",
        photo_prejudice: "des photos de la porte endommagée",
        carte_identite: "des copies de votre pièce d'identité recto-verso",
        facture:
          "des factures acquittées attestant de la réalité des travaux de remise en état à l'identique",
        rib: "des relevés d'identité bancaire",
        extrait_kbis: "des extraits Kbis datant de moins de 3 mois",
        quittance_subrogative:
          "des quittances subrogatives ou tout document établissant la subrogation",
      };

      Object.entries(libelles).forEach(
        ([type, libelle]: [PieceJointeType, string]) => {
          expect(
            TypePieceJointe.depuis(type).libelle({
              court: false,
              titre: false,
              pluriel: true,
              defini: false,
              enCapitales: false,
            }),
          ).toBe(libelle);
        },
      );
    });
  });
  describe("en nom singulier défini cours ", () => {
    it("fonctionne", () => {
      const libelles: { [key in PieceJointeType]?: string } = {
        attestation_information:
          "l'attestation complétée par les forces de l'ordre",
        photo_prejudice: "la photo de la porte endommagée",
        carte_identite: "la pièce d'identité",
        facture: "la facture",
        rib: "le RIB",
        extrait_kbis: "l'extrait Kbis",
        quittance_subrogative: "la quittance subrogative",
      };

      Object.entries(libelles).forEach(
        ([type, libelle]: [PieceJointeType, string]) => {
          expect(
            TypePieceJointe.depuis(type).libelle({
              court: true,
              titre: false,
              pluriel: false,
              defini: true,
              enCapitales: false,
            }),
          ).toBe(libelle);
        },
      );
    });
  });
  describe('en nom singulier court préposé par "de" ', () => {
    it("fonctionne", () => {
      const libelles: { [key in PieceJointeType]?: string } = {
        attestation_information:
          "d'attestation complétée par les forces de l'ordre",
        photo_prejudice: "de photo de la porte endommagée",
        carte_identite: "de pièce d'identité",
        facture: "de facture",
        rib: "de RIB",
        extrait_kbis: "d'extrait Kbis",
        quittance_subrogative: "de quittance subrogative",
      };

      Object.entries(libelles).forEach(
        ([type, libelle]: [PieceJointeType, string]) => {
          expect(
            TypePieceJointe.depuis(type).libelle({
              court: true,
              titre: false,
              pluriel: false,
              defini: false,
              de: true,
              enCapitales: false,
            }),
          ).toBe(libelle);
        },
      );
    });
  });
});
