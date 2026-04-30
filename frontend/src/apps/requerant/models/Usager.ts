import { Personne } from "@/apps/requerant/models/Personne.ts";

export class Usager {
  id: number;
  courriel: string;
  telephone: string;
  nom: string;
  personne?: Personne;
}
