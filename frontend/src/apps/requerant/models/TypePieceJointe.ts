import { Dossier } from "@/apps/requerant/models/Dossier.ts";
import { RapportAuLogement } from "@/apps/requerant/models/RapportAuLogement.ts";
import { TypePersonneMoraleType } from "@/apps/requerant/models/TypePersonneMoraleType.ts";
import { capitaliser } from "@/common/services/divers.ts";

export const PieceJointeTypes = [
  "attestation_information",
  "photo_prejudice",
  "carte_identite",
  "facture",
  "preuve_paiement_facture",
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
  // Courrier d'échange requérant / Ministère
  "courrier_ministere",
  "courrier_requerant",
  "arrete_paiement",
] as const;

export type PieceJointeType = (typeof PieceJointeTypes)[number];

export type ContexteAffichageTypePieceJointe = {
  court: boolean;
  pluriel?: boolean;
  defini?: boolean;
  de?: boolean;
  titre?: boolean;
  dossier?: Dossier;
};

export class TypePieceJointe {
  constructor(
    public readonly type: PieceJointeType,
    protected readonly afficher: (
      contexte: ContexteAffichageTypePieceJointe,
    ) => string,
    // Les pièces jointes de ce type sont-elles ajoutées au dossier après le dépôt, lors de la phase d'échange avec FIP6 ?
    public readonly estEchange: boolean = false,
    // Existe-t-il une seule pièce de ce type ? (cas des courriers d'échange)
    public readonly estUnique: boolean = false,
  ) {}

  private static elements(
    pluriel: boolean,
    feminin: boolean,
    apostrophe: boolean,
    defini: boolean,
    de: boolean,
    titre: boolean,
  ): { article: string; s: string } {
    return {
      article: titre
        ? ""
        : de
          ? apostrophe
            ? "d'"
            : "de "
          : // Afficher en tant que nom
            defini
            ? pluriel
              ? "les "
              : apostrophe
                ? "l'"
                : feminin
                  ? "la "
                  : "le "
            : pluriel
              ? "des "
              : feminin
                ? "une "
                : "un ",
      s: pluriel ? "s" : "",
    };
  }

  public libelle({
    enCapitales = false,
    court = true,
    pluriel = false,
    de = false,
    ...contexte
  }: ContexteAffichageTypePieceJointe & { enCapitales?: boolean }): string {
    return enCapitales
      ? capitaliser(this.afficher({ court, pluriel, de, ...contexte }))
      : this.afficher({ court, pluriel, de, ...contexte });
  }

  public static liste: {
    [key in PieceJointeType]: TypePieceJointe;
  } = {
    attestation_information: new TypePieceJointe(
      "attestation_information",
      (contexte) => {
        const { court, pluriel, defini, de, titre } = {
          pluriel: false,
          defini: false,
          titre: true,
          de: false,
          ...contexte,
        };
        const { s, article } = TypePieceJointe.elements(
          pluriel,
          true,
          true,
          defini,
          de,
          titre,
        );

        return `${article}attestation${s} complétée${s} par les forces de l'ordre`;
      },
    ),
    photo_prejudice: new TypePieceJointe("photo_prejudice", (contexte) => {
      const { pluriel, defini, de, titre } = {
        pluriel: false,
        defini: false,
        titre: true,
        de: false,
        ...contexte,
      };

      const { s, article } = TypePieceJointe.elements(
        pluriel,
        true,
        false,
        defini,
        de,
        titre,
      );

      return `${article}photo${s} de la porte endommagée`;
    }),
    carte_identite: new TypePieceJointe(
      "carte_identite",

      (contexte) => {
        const { court, pluriel, defini, de, titre } = {
          pluriel: false,
          defini: false,
          titre: true,
          de: false,
          ...contexte,
        };
        const { s, article } = TypePieceJointe.elements(
          pluriel,
          true,
          false,
          defini,
          de,
          titre,
        );

        return court
          ? `${article}pièce${s} d'identité${s}`
          : `${article}copie${s} de votre pièce d'identité recto-verso`;
      },
    ),
    facture: new TypePieceJointe("facture", (contexte) => {
      const { court, pluriel, defini, de, titre } = {
        pluriel: false,
        defini: false,
        titre: true,
        de: false,
        ...contexte,
      };
      const { s, article } = TypePieceJointe.elements(
        pluriel,
        true,
        false,
        defini,
        de,
        titre,
      );

      return court
        ? `${article}facture${s}`
        : `${article}facture${s} acquittée${s} attestant de la réalité des travaux de remise en état à l'identique`;
    }),
    preuve_paiement_facture: new TypePieceJointe(
      "preuve_paiement_facture",
      (contexte) => "Reçu attestant le paiement de la facture",
    ),
    rib: new TypePieceJointe("rib", (contexte) => {
      const { court, pluriel, defini, de, titre } = {
        pluriel: false,
        defini: false,
        titre: true,
        de: false,
        ...contexte,
      };
      const { s, article } = TypePieceJointe.elements(
        pluriel,
        false,
        false,
        defini,
        de,
        titre,
      );

      return titre
        ? "RIB"
        : court
          ? `${article}RIB${s}`
          : `${article}relevé${s} d'identité bancaire`;
    }),
    titre_propriete: new TypePieceJointe("titre_propriete", (contexte) => {
      const { court, pluriel, defini, de, titre } = {
        pluriel: false,
        defini: false,
        titre: true,
        de: false,
        ...contexte,
      };
      const { s, article } = TypePieceJointe.elements(
        pluriel,
        true,
        false,
        defini,
        de,
        titre,
      );

      return `${article}titre${s} de propriété`;
    }),
    contrat_location: new TypePieceJointe("contrat_location", (contexte) => {
      const { court, pluriel, defini, de, titre } = {
        pluriel: false,
        defini: false,
        titre: true,
        de: false,
        ...contexte,
      };
      const { s, article } = TypePieceJointe.elements(
        pluriel,
        false,
        false,
        defini,
        de,
        titre,
      );

      return `${article}contrat${s} de location`;
    }),
    non_prise_en_charge_bailleur: new TypePieceJointe(
      "non_prise_en_charge_bailleur",
      (contexte) => {
        const { court, pluriel, defini, de, titre } = {
          pluriel: false,
          defini: false,
          titre: true,
          de: false,
          ...contexte,
        };
        const { s, article } = TypePieceJointe.elements(
          pluriel,
          true,
          false,
          defini,
          de,
          titre,
        );

        return `${article}attestation${s} de non prise en charge par le bailleur`;
      },
    ),
    non_prise_en_charge_assurance: new TypePieceJointe(
      "non_prise_en_charge_assurance",
      (contexte) => {
        const { court, pluriel, defini, de, titre } = {
          pluriel: false,
          defini: false,
          titre: true,
          de: false,
          ...contexte,
        };
        const { s, article } = TypePieceJointe.elements(
          pluriel,
          true,
          false,
          defini,
          de,
          titre,
        );

        return `${article}attestation${s} de non prise en charge par l'assurance${court ? "" : " habitation"}`;
      },
    ),
    // Pièces jointes demandées aux personnes morales
    piece_identite_signataire: new TypePieceJointe(
      "piece_identite_signataire",
      (contexte) => {
        const { court, pluriel, defini, de, titre } = {
          pluriel: false,
          defini: false,
          titre: true,
          de: false,
          ...contexte,
        };
        const { s, article } = TypePieceJointe.elements(
          pluriel,
          true,
          false,
          defini,
          de,
          titre,
        );

        return `${article}pièce${s} d'identité du signataire`;
      },
    ),
    extrait_kbis: new TypePieceJointe("extrait_kbis", (contexte) => {
      const { court, pluriel, defini, de, titre } = {
        pluriel: false,
        defini: false,
        titre: true,
        de: false,
        ...contexte,
      };
      const { s, article } = TypePieceJointe.elements(
        pluriel,
        false,
        true,
        defini,
        de,
        titre,
      );

      return `${article}extrait${s} Kbis${court ? "" : " datant de moins de 3 mois"}`;
    }),
    quittance_subrogative: new TypePieceJointe(
      "quittance_subrogative",
      (contexte) => {
        const { court, pluriel, defini, de, titre } = {
          pluriel: false,
          defini: false,
          titre: true,
          de: false,
          ...contexte,
        };
        const { s, article } = TypePieceJointe.elements(
          pluriel,
          true,
          false,
          defini,
          de,
          titre,
        );

        return `${article}quittance${s} subrogative${s}${court ? "" : " ou tout document établissant la subrogation"}`;
      },
    ),
    pouvoir_signataire: new TypePieceJointe(
      "pouvoir_signataire",
      (contexte) => {
        const { court, pluriel, defini, de, titre } = {
          pluriel: false,
          defini: false,
          titre: true,
          de: false,
          ...contexte,
        };
        const { s, article } = TypePieceJointe.elements(
          pluriel,
          false,
          false,
          defini,
          de,
          titre,
        );

        // TODO renommer selon les cas de PM
        return `${article}justificatif${s} des pouvoirs du signataire`;
      },
    ),
    pv_association_statuts: new TypePieceJointe(
      "pv_association_statuts",
      (contexte) => {
        const { court, pluriel, defini, de, titre } = {
          pluriel: false,
          defini: false,
          titre: true,
          de: false,
          ...contexte,
        };
        const { article } = TypePieceJointe.elements(
          pluriel,
          false,
          false,
          defini,
          de,
          titre,
        );

        return `${article}procès-verba${pluriel ? "ux" : "l"} désignant le représentant légal${court ? "" : " (ou statuts si suffisants)"}`;
      },
    ),
    pv_ag_syndic: new TypePieceJointe("pv_ag_syndic", (contexte) => {
      const { court, pluriel, defini, de, titre } = {
        pluriel: false,
        defini: false,
        titre: true,
        de: false,
        ...contexte,
      };
      const { s, article } = TypePieceJointe.elements(
        pluriel,
        false,
        false,
        defini,
        de,
        titre,
      );

      return `${article}procès-verba${pluriel ? "ux" : "l"} d’assemblée générale désignant le syndic`;
    }),
    declaration_rna_joafe: new TypePieceJointe(
      "declaration_rna_joafe",
      (contexte) => {
        const { court, pluriel, defini, de, titre } = {
          pluriel: false,
          defini: false,
          titre: true,
          de: false,
          ...contexte,
        };
        const { s, article } = TypePieceJointe.elements(
          pluriel,
          false,
          false,
          defini,
          de,
          titre,
        );

        return `${article}récépissé${s} de déclaration RNA${court ? "" : " ou publication JOAFE"}`;
      },
    ),
    identification_etablissement_publique: new TypePieceJointe(
      "identification_etablissement_publique",
      (contexte) => {
        const { court, pluriel, defini, de, titre } = {
          pluriel: false,
          defini: false,
          titre: true,
          de: false,
          ...contexte,
        };
        const { s, article } = TypePieceJointe.elements(
          pluriel,
          false,
          false,
          defini,
          de,
          titre,
        );

        return `${article}document${s} identifiant${s} la collectivité${court ? "" : " (mentionnant le SIREN)"}`;
      },
    ),
    courrier_ministere: new TypePieceJointe(
      "courrier_ministere",
      (contexte) => "Lettre décision dossier",
      true,
      true,
    ),
    courrier_requerant: new TypePieceJointe(
      "courrier_requerant",
      (contexte) => "Déclaration d'acceptation",
      true,
      true,
    ),
    arrete_paiement: new TypePieceJointe(
      "arrete_paiement",
      (contexte) => "Arrêté de paiement",
      true,
      true,
    ),
  };

  public estDemande(
    rapportAuLogement: RapportAuLogement,
    typePersonneMorale?: TypePersonneMoraleType,
    estLieDeclaration: boolean = false,
  ): boolean {
    if (
      this.estRequis(rapportAuLogement, typePersonneMorale, estLieDeclaration)
    ) {
      return true;
    }

    if (this.estEchange || this.type == "preuve_paiement_facture") {
      return false;
    }

    if ("attestation_information" == this.type) {
      return !estLieDeclaration;
    }

    if (rapportAuLogement === "AUTRE") {
      return [
        "non_prise_en_charge_bailleur",
        "contrat_location",
        "titre_propriete",
      ].includes(this.type);
    }

    return false;
  }

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
    if (this.estEchange || this.type == "preuve_paiement_facture") {
      return false;
    }

    if ("attestation_information" == this.type) {
      return !estLieDeclaration;
    }

    if ("non_prise_en_charge_bailleur" == this.type) {
      return rapportAuLogement == "LOCATAIRE";
    }

    if ("contrat_location" == this.type) {
      return ["LOCATAIRE", "BAILLEUR"].includes(rapportAuLogement);
    }

    if (["titre_propriete"].includes(this.type)) {
      return ["PROPRIETAIRE"].includes(rapportAuLogement);
    }

    if (
      [
        "photo_prejudice",
        "carte_identite",
        "facture",
        "rib",
        "photo",
        "non_prise_en_charge_assurance",
      ].includes(this.type)
    ) {
      return true;
    }

    if (typePersonneMorale == "ASSUREUR") {
      // Extrait Kbis de moins de 3 mois
      // Quittance subrogative ou tout document établissant la subrogation
      // Justificatif des pouvoirs du signataire (le cas échéant)
      return ["extrait_kbis", "quittance_subrogative"].includes(this.type);
    }

    if (typePersonneMorale == "SCI") {
      // Extrait Kbis de moins de 3 mois ou attestation d’immatriculation au registre national des entreprises (INPI)
      // Pièce d’identité du représentant mentionné
      // Le cas échéant, délégation de signature
      return ["extrait_kbis", "quittance_subrogative"].includes(this.type);
    }

    if (typePersonneMorale == "BAILLEUR_SOCIAL") {
      // Extrait Kbis de moins de 3 mois ou identification équivalente (SIREN)
      // Justificatif des pouvoirs du signataire (délégation de signature si nécessaire)
      // Pièce d’identité du signataire
      return ["extrait_kbis", "quittance_subrogative"].includes(this.type);
    }

    if (typePersonneMorale == "SYNDIC") {
      // Procès-verbal d’assemblée générale désignant le syndic
      // Le cas échéant, délégation de signature du signataire
      // Pièce d’identité du signataire
      return ["pv_ag_syndic", "quittance_subrogative"].includes(this.type);
    }

    if (typePersonneMorale == "ASSOCIATION") {
      // Récépissé de déclaration ou extrait RNA / publication JOAFE
      // Procès-verbal désignant le représentant légal (ou statuts si suffisants)
      // Le cas échéant, délégation de signature
      // Pièce d’identité du représentant
      return [
        "declaration_rna_joafe",
        "pv_association_status",
        "piece_identite_signataire",
      ].includes(this.type);
    }

    if (typePersonneMorale == "COLLECTIVITE") {
      // Délégation de signature (si le signataire n’est pas le maire ou le président)
      // Identification de la collectivité (notamment SIREN)
      return ["identification_etablissement_publique"].includes(this.type);
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
      return ["identification_etablissement_publique"].includes(this.type);
    }

    return false;
  }

  public equals(other: TypePieceJointe | PieceJointeType): boolean {
    return other instanceof TypePieceJointe
      ? this.type === other.type
      : this.type === other;
  }

  public static depuis(type: PieceJointeType): TypePieceJointe {
    return TypePieceJointe.liste[type];
  }

  public static depuisString(type?: string): TypePieceJointe | undefined {
    return !!type && PieceJointeTypes.includes(type as PieceJointeType)
      ? TypePieceJointe.depuis(type as PieceJointeType)
      : undefined;
  }

  public toString(): string {
    return this.type;
  }
}
