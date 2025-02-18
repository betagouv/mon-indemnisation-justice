import { plainToInstance } from "class-transformer";

export class Redacteur {
  public readonly id: number;
  public readonly nom: string;

  public static catalog: Map<number, Redacteur> = new Map();

  public static resoudre(id: number): null | Redacteur {
    return this.catalog.get(id) ?? null;
  }

  public static charger(data: any): void {
    const instance = plainToInstance(Redacteur, data);

    for (const redacteur of instance instanceof Redacteur
      ? [instance]
      : (instance as Redacteur[])) {
      this.catalog.set(redacteur.id, redacteur);
    }
  }

  public equals(other: Redacteur | null): boolean {
    return this.id === other?.id;
  }
}
