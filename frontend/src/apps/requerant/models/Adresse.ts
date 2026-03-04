import { Commune } from "@/apps/requerant/models/Commune.ts";

export class Adresse {
  ligne1: string;
  ligne2: string;
  commune: Commune = new Commune();

  get libelle(): string {
    return `${this.ligne1}, ${this.commune.codePostal} ${this.commune.nom}`;
  }
}
