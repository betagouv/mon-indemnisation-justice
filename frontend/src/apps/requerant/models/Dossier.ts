import DateTransform from "@/common/normalisation/transformers/DateTransform.ts";
import { Exclude, Expose, Transform, Type } from "class-transformer";
import { Adresse } from "./Adresse";
import { EtatDossier } from "./EtatDossier";
import { PersonneMorale } from "./PersonneMorale";
import { PersonnePhysique } from "./PersonnePhysique";
import { PieceJointe } from "./PieceJointe.ts";
import { type RapportAuLogement } from "./RapportAuLogement";
import { Usager } from "./Usager";

export abstract class BaseDossier {
  // Référence du dossier ou id du brouillon
  reference: string;
  @Type(() => EtatDossier)
  @Exclude({ toPlainOnly: true })
  etatActuel: EtatDossier;
  @Transform(({ value }: { value: any }) =>
    typeof value == "string" ? new Date(value) : undefined,
  )
  dateDepot?: Date;

  get estAccepte(): boolean {
    return this.etatActuel.etat.estAccepte;
  }

  get estSigne(): boolean {
    return this.etatActuel.etat.estSigne;
  }

  get estCloture(): boolean {
    return this.etatActuel.etat.estCloture;
  }

  get estDepose(): boolean {
    return this.etatActuel.etat.estDepose;
  }

  get estEditable(): boolean {
    return this.etatActuel.etat.estEditable;
  }
}

export class Dossier extends BaseDossier {
  // '`Usager` = `Requerant`
  initiePar: Usager;
  @DateTransform()
  dateCreation: Date;

  @Transform(({ obj, value }: { obj: any; value: any }) => {
    if (typeof value === "boolean") {
      return value;
    }

    if ("personneMorale" in obj && !!obj.personneMorale) {
      return true;
    }
    if ("personnePhysique" in obj && !!obj.personnePhysique) {
      return false;
    }

    return undefined;
  })
  @Expose({ toClassOnly: true })
  protected _estPersonneMorale: boolean;

  get estPersonneMorale(): boolean {
    return this._estPersonneMorale;
  }

  set estPersonneMorale(estPersonneMorale: boolean) {
    this._estPersonneMorale = estPersonneMorale;

    if (this.estPersonneMorale && !this.personneMorale) {
      this.personneMorale = new PersonneMorale();
    }

    if (!this.estPersonneMorale && !this.personnePhysique) {
      this.personnePhysique = new PersonnePhysique();
    }
  }

  // '`Requerant` = `PersonnePhysique` | `PersonneMorale`
  @Type(() => PersonnePhysique)
  personnePhysique?: PersonnePhysique = new PersonnePhysique();
  @Type(() => PersonneMorale)
  personneMorale?: PersonneMorale;
  @Type(() => Adresse)
  adresse: Adresse = new Adresse();
  // `RapportAuLogement` = `QualiteRequerant`
  rapportAuLogement: RapportAuLogement;
  descriptionRapportAuLogement?: string;
  @DateTransform(true)
  dateOperation: Date;
  description?: string;
  estPorteBlindee: boolean = false;
  piecesJointes: PieceJointe[];
}

export class DossierApercu extends BaseDossier {}
