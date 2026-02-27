import { Adresse, Commune } from "@/apps/requerant/models";
import { plainToInstance } from "class-transformer";
import { ServiceIdentifier } from "inversify";

export interface AdresseManagerInterface {
  listerCommunes(codePostal: string): Promise<Commune[]>;

  suggererAdresse(requete: string): Promise<Adresse[]>;
}

export namespace AdresseManagerInterface {
  export const $: ServiceIdentifier<AdresseManagerInterface> = Symbol(
    "AdresseManagerInterface",
  );
}

export class APIAdresseManager implements AdresseManagerInterface {
  async listerCommunes(codePostal: string): Promise<Commune[]> {
    const response = await fetch(`/api/requerant/communes/${codePostal}`);

    const data = await response.json();

    return plainToInstance(
      Commune,
      data as { id: number; code: string; nom: string }[],
    );
  }

  async suggererAdresse(requete: string): Promise<Adresse[]> {
    // Documentation API Auto-complétion adresse https://geoservices.ign.fr/documentation/services/services-geoplateforme/autocompletion
    const response = await fetch(
      "https://data.geopf.fr/geocodage/completion/?" +
        new URLSearchParams({
          text: requete,
          type: "StreetAddress",
          maximumResponses: String(5),
        }).toString(),
    );

    const {
      status,
      message,
      results,
    }: {
      status?: string;
      message?: string;
      results: [
        {
          city: string;
          zipcode: string;
          fulltext: string;
        },
      ];
    } = await response.json();

    if (status !== "OK") {
      throw new Error(message);
    }

    return plainToInstance(
      Adresse,
      results.map(({ city, zipcode, fulltext }) => ({
        ligne1: fulltext.split(",").at(0) || "",
        commune: city,
        codePostal: zipcode,
      })),
    );
  }
}
