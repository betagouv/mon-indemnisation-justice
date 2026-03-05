import { Transform, Type } from "class-transformer";
import { Adresse } from "./Adresse";
import { EtatDossier } from "./EtatDossier";
import { PersonneMorale } from "./PersonneMorale";
import { PersonnePhysique } from "./PersonnePhysique";
import { type RapportAuLogement } from "./RapportAuLogement";
import { Usager } from "./Usager";
import { PieceJointe } from "./PieceJointe.ts";

export abstract class BaseDossier {
  // Référence du dossier ou id du brouillon
  reference: string;
  @Type(() => EtatDossier)
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
  // '`Requerant` = `PersonnePhysique` | `PersonneMorale`
  requerant: PersonnePhysique | PersonneMorale;
  adresse: Adresse = new Adresse();
  // `RapportAuLogement` = `QualiteRequerant`
  rapportAuLogement: RapportAuLogement;
  descriptionRapportAuLogement?: string;
  @Transform(({ value }: { value: any }) =>
    typeof value == "string" ? new Date(value) : undefined,
  )
  dateOperation: Date;
  description: string;
  estPorteBlindee: boolean;
  piecesJointes: PieceJointe[];

  get estPersonneMorale(): boolean {
    return "raisonSociale" in this.requerant;
  }
}

export class DossierApercu extends BaseDossier {}
