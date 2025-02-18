import { plainToInstance } from "class-transformer";

export class EtatDossier {
  public readonly id: string;
  public readonly slug: string;
  public readonly libelle: string;

  public static catalog: Map<string, EtatDossier> = new Map();

  public static resoudreParSlug(slug: string): null | EtatDossier {
    return this.catalog.values().find((e) => e.slug == slug) ?? null;
  }

  public static resoudre(id: string): null | EtatDossier {
    return this.catalog.get(id) ?? null;
  }

  public static charger(data: []): void {
    for (const etat of plainToInstance(EtatDossier, data)) {
      this.catalog.set(etat.id, etat);
    }
  }
}
