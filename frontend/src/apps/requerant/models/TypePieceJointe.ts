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
  };

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
