import { Civilite } from "./Civilite.ts";

export class Personne {
  id: string;
  civilite: Civilite;
  nom: string;
  nomNaissance: string;
  prenom: string;
  prenoms?: string[] = [];
  courriel: string;
  telephone?: string;
}
