import { estAujourdhui, memeJour } from "@/common/services/date";
import { Transform } from "class-transformer";
import {
  Agent,
  DossierDetail,
  TypeInstitutionSecuritePublique,
} from "@/common/models";
import { TypeAttestation } from "@/common/models/Dossier.ts";

export class DocumentType {
  private constructor(
    public readonly type: string,
    public readonly libelle: string,
    // Indique s'il n'y a qu'un seul document de ce type par dossier ou non
    public readonly estUnique: boolean = false,
    // Indique si les documents de ce type sont à transmettre par le requérant
    public readonly estRequerant: boolean = true,
  ) {}

  public estPieceJointe(): boolean {
    return !this.estDocumentEchange();
  }
  public estDocumentEchange(): boolean {
    return [
      DocumentType.TYPE_COURRIER_MINISTERE,
      DocumentType.TYPE_COURRIER_REQUERANT,
      DocumentType.TYPE_ARRETE_PAIEMENT,
    ].includes(this);
  }

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

  public static readonly TYPE_NON_PRISE_EN_CHARGE_ASSURANCE = new DocumentType(
    "non_prise_en_charge_assurance",
    "Attestation de non prise en charge par l'assurance",
  );

  public static readonly TYPE_NON_PRISE_EN_CHARGE_BAILLEUR = new DocumentType(
    "non_prise_en_charge_bailleur",
    "Attestation de non prise en charge par le bailleur",
  );

  public static readonly TYPE_COURRIER_MINISTERE = new DocumentType(
    "courrier_ministere",
    "Courrier de décision signé du Ministere",
    true,
  );

  public static readonly TYPE_COURRIER_REQUERANT = new DocumentType(
    "courrier_requerant",
    "Courrier de décision signé du requérant",
    true,
    true,
  );

  public static readonly TYPE_ARRETE_PAIEMENT = new DocumentType(
    "arrete_paiement",
    "Arrêté de paiement",
    true,
  );
}

export interface MetaDonneesAttestation {
  typeAttestation?: TypeAttestation;
  typeInstitutionSecuritePublique?: TypeInstitutionSecuritePublique;
  dateOperation?: Date;
}

export class Document {
  public readonly id: number;
  public readonly originalFilename: string;
  public readonly mime: string;
  public readonly size?: number;
  public readonly estAjoutRequerant?: boolean;

  public corps?: string;
  public readonly filename: string;
  public fileHash: string;
  @Transform(({ value }: { value: string }): DocumentType | undefined =>
    Document.types.find((type: DocumentType) => type.type === value),
  )
  public readonly type: DocumentType;
  public metaDonnees: MetaDonneesAttestation | any;
  @Transform(({ value }) => new Date(value))
  public readonly dateDerniereModification?: Date;

  public estEditable(dossier: DossierDetail, agent: Agent): boolean {
    return (
      this.estAjoutRequerant === false &&
      (agent.estValidateur() || agent.instruit(dossier))
    );
  }

  get url(): string {
    return `/agent/document/${this.id}/${this.fileHash}`;
  }

  get typeFichier(): string {
    switch (this.mime) {
      case "application/pdf":
        return "pdf";
      case "image/jpeg":
        return "jpg";
      case "image/png":
        return "png";
      case "image/gif":
        return "gif";

      default:
        return "";
    }
  }

  get tailleFichier(): string {
    if (!this.size) {
      return "";
    }

    const k = 1024;
    const sizes = ["o", "ko", "mo", "go", "to"];
    const i = Math.floor(Math.log(this.size) / Math.log(k));
    return parseFloat((this.size / Math.pow(k, i)).toFixed(2)) + " " + sizes[i];
  }

  get infoFichier(): string {
    return `${this.type?.libelle || ""} - ${this.typeFichier?.toUpperCase()}${this.size ? " - " + this.tailleFichier : ""}`;
  }

  public estAJour(): boolean {
    return (
      !!this.dateDerniereModification &&
      estAujourdhui(this.dateDerniereModification)
    );
  }

  public estPDF(): boolean {
    return "application/pdf" === this.mime;
  }

  public static types: DocumentType[] = [
    DocumentType.TYPE_ATTESTATION_INFORMATION,
    DocumentType.TYPE_PHOTO_PREJUDICE,
    DocumentType.TYPE_CARTE_IDENTITE,
    DocumentType.TYPE_FACTURE,
    DocumentType.TYPE_PREUVE_PAIEMENT_FACTURE,
    DocumentType.TYPE_RIB,
    DocumentType.TYPE_TITRE_PROPRIETE,
    DocumentType.TYPE_CONTRAT_LOCATION,
    DocumentType.TYPE_NON_PRISE_EN_CHARGE_ASSURANCE,
    DocumentType.TYPE_NON_PRISE_EN_CHARGE_BAILLEUR,
    DocumentType.TYPE_COURRIER_MINISTERE,
    DocumentType.TYPE_COURRIER_REQUERANT,
    DocumentType.TYPE_ARRETE_PAIEMENT,
  ];
}
