import { TypePieceJointe } from "@/apps/requerant/models/TypePieceJointe.ts";
import DateTransform from "@/common/normalisation/transformers/DateTransform.ts";
import UndefinedTransform from "@/common/normalisation/transformers/UndefinedTransform.ts";
import { Expose, Transform, Type } from "class-transformer";
import { Adresse } from "./Adresse";
import { EtatDossier } from "./EtatDossier";
import { PersonneMorale } from "./PersonneMorale";
import { PersonnePhysique } from "./PersonnePhysique";
import { PieceJointe } from "./PieceJointe.ts";
import { type RapportAuLogement } from "./RapportAuLogement";
import { Usager } from "./Usager";

export abstract class BaseDossier {
  // Référence du dossier ou id du brouillon
  @Expose({ toClassOnly: true })
  public readonly id: number;
  @Expose({ toClassOnly: true })
  public readonly reference?: string;
  @Type(() => EtatDossier)
  @Expose({ toClassOnly: true })
  etatActuel: EtatDossier;
  @DateTransform()
  dateDepot?: Date;

  get estBrouillon(): boolean {
    return !this.dateDepot && !this.reference;
  }

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

  protected _estPersonneMorale: boolean;

  get estPersonneMorale(): boolean {
    return this._estPersonneMorale;
  }

  @Expose({ toClassOnly: true })
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
  @UndefinedTransform()
  descriptionRapportAuLogement?: string;
  @DateTransform(true)
  dateOperation: Date;
  @UndefinedTransform()
  description?: string;
  estPorteBlindee: boolean = false;
  @Type(() => PieceJointe)
  piecesJointes: PieceJointe[];
  idTestEligibilite?: number;
  idDeclarationFDO?: string;

  public compterPiecesJointesDeType(type: TypePieceJointe): number {
    return this.getPiecesJointesDeType(type).length;
  }

  public getPiecesJointesDeType(type: TypePieceJointe): PieceJointe[] {
    return this.piecesJointes.filter((pieceJointe: PieceJointe) => {
      return pieceJointe.type.equals(type);
    });
  }

  public estLieDeclaration(): boolean {
    return !!this.idDeclarationFDO;
  }
}

export class DossierApercu extends BaseDossier {}
