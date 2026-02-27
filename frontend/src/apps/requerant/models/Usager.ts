import { Personne } from "@/apps/requerant/models/Personne.ts";

export class Usager {
  id: number;
  prenom: string;
  nom: string;
  courriel: string;
  personne?: Personne;
}
