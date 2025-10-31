import {
  Exclude,
  Expose,
  plainToInstance,
  Transform,
  Type,
} from "class-transformer";
import { Agent, Redacteur } from "@/common/models";
import DateTransform from "@/common/normalisation/transformers/DateTransform.ts";

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
  @DateTransform()
  public readonly dateCreation: Date;
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
