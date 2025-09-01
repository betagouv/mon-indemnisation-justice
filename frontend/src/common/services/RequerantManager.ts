import { injectable } from "inversify";

export interface RequerantManagerInterface {
  estAdresseCourrielAttribuee(adresse: string): Promise<boolean>;
}

export const RequerantManagerImpl = Symbol.for("RequerantManagerInterface");

@injectable()
export class RequerantAPICLient implements RequerantManagerInterface {
  private static adresseRegex: RegExp =
    /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

  protected registre: Map<string, boolean> = new Map();

  async estAdresseCourrielAttribuee(adresse: string): Promise<boolean> {
    if (!adresse.match(RequerantAPICLient.adresseRegex)) {
      return undefined;
    }

    if (this.registre.has(adresse)) {
      return this.registre.get(adresse);
    }

    try {
      const response = await fetch("/bris-de-porte/tester-adresse-courriel", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify({
          adresse,
        }),
      });
      if (response.ok) {
        const data = await response.json();

        this.registre.set(adresse, (data.disponible as boolean) ?? false);
        return this.registre.get(adresse);
      }

      return false;
    } catch (error) {
      console.error(error);
      return true;
    }
  }
}
