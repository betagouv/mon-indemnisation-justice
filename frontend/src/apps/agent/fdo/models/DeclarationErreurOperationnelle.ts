import { Transform } from "class-transformer";

class Adresse {
  public ligne1: "";
  public ligne2: "";
  public codePostal: "";
  public localite: "";
}

class Requerant {
  nom: string;
  prenom: string;
  telephone: string;
  courriel: string;
  message: string;
}

class Procedure {
  numeroProcedure: string;
  serviceEnqueteur: string;
  courrielAgent: string;
  telephoneAgent: string;
  juridictionOuParquet: string;
  nomMagistrat: string;
  commentaire: string;
}

export class DeclarationErreurOperationnelle {
  public readonly id?: string;
  @Transform(({ value }) => new Date(value), { toClassOnly: true })
  @Transform(({ value }: { value: Date }) => value.getTime(), {
    toPlainOnly: true,
  })
  public readonly dateCreation: Date;
  @Transform(({ value }) => new Date(value), { toClassOnly: true })
  @Transform(({ value }: { value: Date }) => value.getTime(), {
    toPlainOnly: true,
  })
  public dateOperation: Date;
  adresse?: Adresse;
  requerant?: Requerant;
  procedure?: Procedure;

  public constructor() {
    this.dateOperation = new Date();
  }

  public get reference(): string {
    return this.id ?? this.dateCreation.getTime().toString();
  }

  public estBrouillon(): boolean {
    return !this.id;
  }
}
