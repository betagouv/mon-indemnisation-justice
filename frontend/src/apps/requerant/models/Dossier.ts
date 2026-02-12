import { Usager } from "./Usager.ts";
import { Requerant } from ".//Requerant.ts";
import { Adresse } from "./Adresse.ts";
import { EtatDossier } from "@/apps/requerant/models/EtatDossier.ts";
import { Type } from "class-transformer";

export abstract class BaseDossier {
  reference: string;
  @Type(() => EtatDossier)
  etatActuel: EtatDossier;

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
  initiePar: Usager;
  requerant: Requerant = new Requerant();
  adresse: Adresse = new Adresse();
  dateOperation: Date;
  description: string;
}

export class DossierApercu extends BaseDossier {}
