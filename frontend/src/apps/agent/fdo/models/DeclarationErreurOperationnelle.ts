import { Exclude, Expose, Transform, Type } from "class-transformer";
import { Agent } from "@/common/models";
import DateTransform from "@/common/normalisation/transformers/DateTransform.ts";

class Adresse {
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

class InformationsRequerant {
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
  message: string = "";
}

class Procedure {
  numeroProcedure: string = "";
  serviceEnqueteur: string = "";
  juridictionOuParquet: string = "";
  nomMagistrat: string = "";
}

export class DeclarationErreurOperationnelle {
  @Exclude({ toPlainOnly: true })
  public readonly id?: string;
  @DateTransform()
  public readonly dateCreation: Date;
  public doute: boolean = false;
  public motifDoute?: string;
  @DateTransform() public dateOperation: Date;
  @DateTransform()
  @Exclude({ toPlainOnly: true })
  public dateSoumission?: Date;
  @Type(() => Adresse)
  adresse: Adresse = new Adresse();
  @Expose({ toClassOnly: true })
  @Type(() => Agent)
  public readonly agent?: Agent;
  telephone: string;
  @Expose({ name: "enPresenceRequerant" })
  @Transform(
    ({ obj }) => {
      return !!obj.infosRequerant;
    },
    { toClassOnly: true },
  )
  enPresenceRequerant?: boolean;
  @Type(() => InformationsRequerant)
  infosRequerant?: InformationsRequerant;
  procedure: Procedure = new Procedure();

  public constructor() {
    this.dateOperation = new Date();
    this.dateCreation = new Date();
  }

  public get reference(): string {
    return this.id ?? this.dateCreation.getTime().toString();
  }

  public estSauvegarde(): boolean {
    return !!this.id;
  }

  public estBrouillon(): boolean {
    return !this.estSauvegarde();
  }
}
