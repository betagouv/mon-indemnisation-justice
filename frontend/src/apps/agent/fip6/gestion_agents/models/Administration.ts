import { plainToInstance } from "class-transformer";

export class Administration {
  public readonly id: string;
  public readonly libelle: string;
  public readonly estLibelleFeminin: boolean;
  public readonly roles: string[];

  public static catalog: Map<string, Administration> = new Map();

  public static resoudre(id: string): null | Administration {
    return this.catalog.get(id) ?? null;
  }

  public static charger(data: any): void {
    const instance = plainToInstance(Administration, data);
    const administrations: Administration[] =
      instance instanceof Administration
        ? [instance]
        : (instance as Administration[]);

    for (const administration of administrations) {
      this.catalog.set(administration.id, administration);
    }
  }
}
