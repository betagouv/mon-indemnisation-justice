import { Exclude, Expose, Transform, Type } from "class-transformer";
import { Agent } from "@/common/models";

class Adresse {
  public ligne1: string = "";
  public ligne2: string = "";
  public codePostal: string = "";
  public localite: string = "";

  public libelle(): string {
    return `${this.ligne1} ${this.codePostal} ${this.localite}`;
  }
}

class InformationsRequerant {
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
  @Transform(({ value }) => (value ? new Date(value) : undefined), {
    toClassOnly: true,
  })
  @Transform(({ value }: { value: Date }) => value?.getTime(), {
    toPlainOnly: true,
  })
  @Exclude({ toPlainOnly: true })
  public dateSoumission?: Date;
  @Expose()
  @Type(() => Adresse)
  adresse: Adresse = new Adresse();
  @Expose({ toClassOnly: true })
  public readonly agent?: Agent;
  infosRequerant: InformationsRequerant = new InformationsRequerant();
  procedure: Procedure = new Procedure();
  commentaire: string = "";

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
