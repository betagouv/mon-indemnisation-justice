import { injectable } from "inversify";

export interface RequerantManagerInterface {
  estAdresseCourrielAttribuee(adresse: string): Promise<boolean>;
}

export const RequerantManagerImpl = Symbol.for("RequerantManagerInterface");

@injectable()
export class RequerantAPICLient implements RequerantManagerInterface {
  private static adresseRegex: RegExp =
    /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
  async estAdresseCourrielAttribuee(adresse: string): Promise<boolean> {
    if (!adresse.match(RequerantAPICLient.adresseRegex)) {
      return undefined;
    }

    try {
      const response = await fetch(
        "/bris-de-porte/tester-adresse-courriel.json",
        {
          method: "POST",
          body: JSON.stringify({
            adresse,
          }),
        },
      );
      const data = await response.json();

      return (data.disponible as boolean) ?? false;
    } catch (error) {
      console.error(error);
      return false;
    }
  }
}
