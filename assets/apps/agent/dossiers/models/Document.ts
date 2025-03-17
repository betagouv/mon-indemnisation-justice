import { Transform } from "class-transformer";

export class DocumentType {
  private constructor(
    public readonly type: string,
    public readonly libelle: string,
    // Indique si les documents de ce type sont à transmettre par le requérant
    public readonly estRequerant: boolean = true,
  ) {}

  public static readonly TYPE_ATTESTATION_INFORMATION = new DocumentType(
    "attestation_information",
    "Attestation à remettre en cas d'erreur de porte",
  );
  public static readonly TYPE_PHOTO_PREJUDICE = new DocumentType(
    "photo_prejudice",
    "Photo de la porte endommagée",
  );
  public static readonly TYPE_CARTE_IDENTITE = new DocumentType(
    "carte_identite",
    "Pièce d'identité",
  );
  public static readonly TYPE_FACTURE = new DocumentType("facture", "Facture");
  public static readonly TYPE_PREUVE_PAIEMENT_FACTURE = new DocumentType(
    "preuve_paiement_facture",
    "Preuve de paiement",
  );
  public static readonly TYPE_RIB = new DocumentType("rib", "RIB / IBAN");
  public static readonly TYPE_TITRE_PROPRIETE = new DocumentType(
    "titre_propriete",
    "Titre de propriété",
  );
  public static readonly TYPE_CONTRAT_LOCATION = new DocumentType(
    "contrat_location",
    "Contrat de location",
  );

  public static readonly TYPE_COURRIER_MINISTERE = new DocumentType(
    "courrier_ministere",
    "Courrier signé de décision du Ministere",
  );
}

export class Document {
  public readonly id: number;
  public readonly mime: string;
  public readonly originalFilename: string;
  public readonly url: string;
  @Transform(({ value }: { value: string }) =>
    Document.types.find((type: DocumentType) => type.type == value),
  )
  public readonly type: DocumentType;

  public static types: DocumentType[] = [
    DocumentType.TYPE_ATTESTATION_INFORMATION,
    DocumentType.TYPE_PHOTO_PREJUDICE,
    DocumentType.TYPE_CARTE_IDENTITE,
    DocumentType.TYPE_FACTURE,
    DocumentType.TYPE_PREUVE_PAIEMENT_FACTURE,
    DocumentType.TYPE_RIB,
    DocumentType.TYPE_TITRE_PROPRIETE,
    DocumentType.TYPE_CONTRAT_LOCATION,
    DocumentType.TYPE_COURRIER_MINISTERE,
  ];
}
