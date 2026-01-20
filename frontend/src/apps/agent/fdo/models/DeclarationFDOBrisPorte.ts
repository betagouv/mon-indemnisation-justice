import { Exclude, Expose, Transform, Type } from "class-transformer";
import { Agent } from "@/common/models";
import DateTransform from "@/common/normalisation/transformers/DateTransform.ts";

export class Adresse {
  public ligne1: string = "";
  public ligne2: string = "";
  public codePostal: string = "";
  public localite: string = "";
}

export class Civilite {
  static readonly M = new Civilite("M", "Monsieur");
  static readonly MME = new Civilite("MME", "Madame");

  protected constructor(
    public readonly id: string,
    public readonly libelle: string,
  ) {}

  static liste(): Civilite[] {
    return [this.M, this.MME];
  }

  static from(id: string): Civilite | undefined {
    return Civilite.liste().find((c: Civilite) => c.id === id);
  }
}

export class CoordonneesRequerant {
  @Transform(
    ({ value }: { value?: Civilite }) => {
      return value?.id;
    },
    { toPlainOnly: true },
  )
  @Transform(
    ({ value }: { value: string | Civilite }) => {
      return value instanceof Civilite ? value : Civilite.from(value);
    },
    { toClassOnly: true },
  )
  civilite?: Civilite;
  nom: string = "";
  prenom: string = "";
  telephone: string = "";
  courriel: string = "";
}

export class Procedure {
  numeroProcedure: string = "";
  serviceEnqueteur: string = "";
  juridictionOuParquet: string = "";
  nomMagistrat: string = "";
  telephone: string;
}

export const DeclarationFDOBrisPorteErreurTypes = <const>[
  "OUI",
  "NON",
  "DOUTE",
];
export type DeclarationFDOBrisPorteErreurType =
  (typeof DeclarationFDOBrisPorteErreurTypes)[number];

export class DeclarationFDOBrisPorte {
  public readonly id: string;

  @DateTransform()
  public readonly dateCreation: Date;

  public estErreur?: DeclarationFDOBrisPorteErreurType;

  public descriptionErreur?: string;

  @DateTransform(true)
  @Expose()
  public dateOperation?: Date;

  @DateTransform()
  @Expose({ toClassOnly: true })
  public dateSoumission?: Date;

  @Type(() => Adresse)
  adresse?: Adresse;

  @Expose({ toClassOnly: true })
  @Type(() => Agent)
  public readonly agent?: Agent;

  @Expose({ name: "enPresenceRequerant" })
  @Transform(
    ({ obj }) => {
      return !!obj.coordonneesRequerant;
    },
    { toClassOnly: true },
  )
  enPresenceRequerant?: boolean;

  @Expose()
  precisionsRequerant?: string;

  @Type(() => CoordonneesRequerant)
  coordonneesRequerant?: CoordonneesRequerant;

  procedure: Procedure = new Procedure();

  public constructor() {
    this.dateCreation = new Date();
  }

  public get reference(): string {
    return this.id;
  }

  public estSoumise(): boolean {
    return !!this.dateSoumission;
  }

  public estBrouillon(): boolean {
    return !this.dateSoumission;
  }
}
