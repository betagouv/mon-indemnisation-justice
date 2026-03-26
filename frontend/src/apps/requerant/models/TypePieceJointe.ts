import { RapportAuLogement } from "@/apps/requerant/models/RapportAuLogement.ts";
import { TypePersonneMoraleType } from "@/apps/requerant/models/TypePersonneMoraleType.ts";

export const PieceJointeTypes = [
  "attestation_information",
  "photo_prejudice",
  "carte_identite",
  "facture",
  "rib",
  "titre_propriete",
  "contrat_location",
  "non_prise_en_charge_bailleur",
  "non_prise_en_charge_assurance",
  // Pièces jointes demandées aux personnes morales
  "piece_identite_signataire",
  "extrait_kbis",
  "quittance_subrogative",
  "pouvoir_signataire",
  "pv_association_statuts",
  "pv_ag_syndic",
  "declaration_rna_joafe",
  "identification_etablissement_publique",
] as const;

export type PieceJointeType = (typeof PieceJointeTypes)[number];

export class TypePieceJointe {
  constructor(
    public readonly type: PieceJointeType,
    public readonly libelle: string,
  ) {}

  public static liste: {
    [key in PieceJointeType]: TypePieceJointe;
  } = {
    attestation_information: new TypePieceJointe(
      "attestation_information",
      "Attestation complétée par les forces de l'ordre",
    ),
    photo_prejudice: new TypePieceJointe(
      "photo_prejudice",
      "Photos de la porte endommagée", //
    ),
    carte_identite: new TypePieceJointe(
      "carte_identite",
      "Copie de votre pièce d'identité recto-verso",
    ),
    facture: new TypePieceJointe(
      "facture",
      "Facture acquittée attestant de la réalité des travaux de remise en état à l'identique",
    ),
    rib: new TypePieceJointe("rib", "Relevé d'identité bancaire"),
    titre_propriete: new TypePieceJointe(
      "titre_propriete",
      "Titre de propriété",
    ),
    contrat_location: new TypePieceJointe(
      "contrat_location",
      "Contrat de location",
    ),
    non_prise_en_charge_bailleur: new TypePieceJointe(
      "non_prise_en_charge_bailleur",
      "Attestation de non prise en charge par le bailleur",
    ),
    non_prise_en_charge_assurance: new TypePieceJointe(
      "non_prise_en_charge_assurance",
      "Attestation de non prise en charge par l'assurance habitation",
    ),
    // Pièces jointes demandées aux personnes morales
    piece_identite_signataire: new TypePieceJointe(
      "piece_identite_signataire",
      "Pièce d'identité du signataire",
    ),
    extrait_kbis: new TypePieceJointe("extrait_kbis", ""),
    quittance_subrogative: new TypePieceJointe("quittance_subrogative", ""),
    pouvoir_signataire: new TypePieceJointe("pouvoir_signataire", ""),
    pv_association_statuts: new TypePieceJointe("pv_association_statuts", ""),
    pv_ag_syndic: new TypePieceJointe("pv_ag_syndic", ""),
    declaration_rna_joafe: new TypePieceJointe("declaration_rna_joafe", ""),
    identification_etablissement_publique: new TypePieceJointe(
      "identification_etablissement_publique",
      "",
    ),
  };

  /**
   * Est-ce que le type de pièce jointe est requis dans le contexte du dossier ?
   *
   * @param rapportAuLogement
   * @param typePersonneMorale
   * @param estLieDeclaration
   */
  public estRequis(
    rapportAuLogement: RapportAuLogement,
    typePersonneMorale?: TypePersonneMoraleType,
    estLieDeclaration: boolean = false,
  ): boolean {
    if ("attestation_information" == this.type) {
      return !estLieDeclaration;
    }

    if ("non_prise_en_charge_bailleur" == this.type) {
      return ["LOCATAIRE", "AUTRE"].includes(rapportAuLogement);
    }

    if ("contrat_location" == this.type) {
      return ["LOCATAIRE", "BAILLEUR", "AUTRE"].includes(rapportAuLogement);
    }

    if (["titre_propriete"].includes(this.type)) {
      return ["PROPRIETAIRE", "AUTRE"].includes(rapportAuLogement);
    }

    if (
      ["photo_prejudice", "carte_identite", "facture", "rib", "photo"].includes(
        this.type,
      )
    ) {
      return true;
    }

    if (typePersonneMorale == "ASSUREUR") {
      // Extrait Kbis de moins de 3 mois
      // Quittance subrogative ou tout document établissant la subrogation
      // Justificatif des pouvoirs du signataire (le cas échéant)
      return [
        "extrait_kbis",
        "quittance_subrogative",
        "pouvoir_signature",
      ].includes(this.type);
    }

    if (typePersonneMorale == "SCI") {
      // Extrait Kbis de moins de 3 mois ou attestation d’immatriculation au registre national des entreprises (INPI)
      // Pièce d’identité du représentant mentionné
      // Le cas échéant, délégation de signature
      return [
        "extrait_kbis",
        "quittance_subrogative",
        "pouvoir_signature",
      ].includes(this.type);
    }

    if (typePersonneMorale == "BAILLEUR_SOCIAL") {
      // Extrait Kbis de moins de 3 mois ou identification équivalente (SIREN)
      // Justificatif des pouvoirs du signataire (délégation de signature si nécessaire)
      // Pièce d’identité du signataire
      return [
        "extrait_kbis",
        "quittance_subrogative",
        "pouvoir_signature",
      ].includes(this.type);
    }

    if (typePersonneMorale == "SYNDIC") {
      // Procès-verbal d’assemblée générale désignant le syndic
      // Le cas échéant, délégation de signature du signataire
      // Pièce d’identité du signataire
      return [
        "pv_ag_syndic",
        "quittance_subrogative",
        "pouvoir_signature",
      ].includes(this.type);
    }

    if (typePersonneMorale == "ASSOCIATION") {
      // Récépissé de déclaration ou extrait RNA / publication JOAFE
      // Procès-verbal désignant le représentant légal (ou statuts si suffisants)
      // Le cas échéant, délégation de signature
      // Pièce d’identité du représentant
      return [
        "declaration_rna_joafe",
        "pv_association_status",
        "pouvoir_signature",
        "piece_identite_signataire",
      ].includes(this.type);
    }

    if (typePersonneMorale == "COLLECTIVITE") {
      // Délégation de signature (si le signataire n’est pas le maire ou le président)
      // Identification de la collectivité (notamment SIREN)
      return [
        "pouvoir_signature",
        "identification_etablissement_publique",
      ].includes(this.type);
    }

    if (typePersonneMorale == "ENTREPRISE_PRIVEE") {
      // Extrait Kbis de moins de 3 mois
      // Le cas échéant, délégation de signature
      // Pièce d’identité du signataire
      return [
        "extrait_kbis",
        "quittance_subrogative",
        "piece_identite_signataire",
      ].includes(this.type);
    }

    if (typePersonneMorale == "ETABLISSEMENT_PUBLIC") {
      // Identification de l’établissement (notamment SIREN)
      // Justificatif des pouvoirs du signataire (délégation ou décision de nomination)
      return [
        "pouvoir_signature",
        "identification_etablissement_publique",
      ].includes(this.type);
    }

    return false;
  }

  public equals(other: TypePieceJointe): boolean {
    return this.type === other.type;
  }

  public static depuis(type: PieceJointeType): TypePieceJointe {
    return TypePieceJointe.liste[type];
  }

  public toString(): string {
    return this.type;
  }
}
