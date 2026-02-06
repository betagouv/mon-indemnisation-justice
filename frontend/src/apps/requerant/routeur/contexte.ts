import { Usager } from "@/apps/requerant/models/Usager.ts";

export interface ContexteUsager {
  usager: Usager;
  incarnePar?: string;
}
