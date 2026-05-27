import { capitaliser } from "@/common/services/divers.ts";
import { Civilite } from "./Requerant";

export class Usager {
  public id: number;
  public civilite: Civilite;
  public nom: string;
  public prenom: string;

  public nomSimple(): string {
    return `${capitaliser(this.prenom)} ${this.nom.toUpperCase()}`;
  }
}
