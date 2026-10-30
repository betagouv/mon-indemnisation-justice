import { DeclarationFDOBrisPorte } from "@fdo/modeles/DeclarationFDOBrisPorte";
import { queryClient } from "@fdo/query";
import { instanceToPlain, plainToInstance } from "class-transformer";
import { ServiceIdentifier } from "inversify";
import { merge } from "ts-deepmerge";

export interface DeclarationManagerInterface {
  getListe(): Promise<DeclarationFDOBrisPorte[]>;

  aDeclaration(id: string): Promise<boolean>;

  getDeclaration(id: string): Promise<DeclarationFDOBrisPorte | undefined>;

  nouvelleDeclaration(): Promise<DeclarationFDOBrisPorte>;

  /** Met à jour la déclaration en mémoire uniquement (aucun appel à l'API). */
  mettreAJour(
    declaration: DeclarationFDOBrisPorte,
    miseAJour?: Partial<DeclarationFDOBrisPorte> | DeclarationFDOBrisPorte,
  ): void;

  enregistrer(
    declaration: DeclarationFDOBrisPorte,
    miseAJour?: Partial<DeclarationFDOBrisPorte>,
  ): Promise<void>;

  soumettre(declaration: DeclarationFDOBrisPorte): Promise<void>;

  supprimer(declaration: DeclarationFDOBrisPorte): Promise<void>;

  /** Force le rechargement des déclarations depuis l'API au prochain accès. */
  invalider(): Promise<void>;
}

export namespace DeclarationManagerInterface {
  export const $: ServiceIdentifier<DeclarationManagerInterface> = Symbol(
    "DeclarationManagerInterface",
  );
}

export class APIDeclarationManager implements DeclarationManagerInterface {
  /** Clé de cache `tanstack-query` de la liste des déclarations de l'agent,
   * alimentée par chaque mutation de ce service. */
  protected static readonly CLEF_CACHE = [
    "DeclarationManagerInterface",
    "declarations",
  ] as const;

  protected recupererDeclarations(): Promise<DeclarationFDOBrisPorte[]> {
    return queryClient.query({
      queryKey: APIDeclarationManager.CLEF_CACHE,
      queryFn: async (): Promise<DeclarationFDOBrisPorte[]> => {
        const reponse = await fetch(
          "/api/agent/fdo/bris-de-porte/mes-declarations",
          {
            method: "GET",
            headers: {
              Accept: "application/json",
            },
          },
        );

        if (!reponse.ok) {
          throw new Error(
            `Échec de la récupération des déclarations : ${reponse.status}`,
          );
        }

        return plainToInstance(
          DeclarationFDOBrisPorte,
          (await reponse.json()) as any[],
        ).sort(
          // Tri par date de dernier changement du plus récent au plus ancien
          (a, b) =>
            (b.etat.date || b.dateCreation).getTime() -
            (a.etat.date || a.dateCreation).getTime(),
        );
      },
      staleTime: 5 * 60 * 1000, // 5 minutes
    });
  }

  /** Remplace (ou ajoute, si absente) la déclaration dans le cache. */
  protected enregistrerDeclaration(declaration: DeclarationFDOBrisPorte): void {
    queryClient.setQueryData<DeclarationFDOBrisPorte[]>(
      APIDeclarationManager.CLEF_CACHE,
      (declarations = []) =>
        declarations.some((d) => d.id === declaration.id)
          ? declarations.map((d) => (d.id === declaration.id ? declaration : d))
          : [...declarations, declaration],
    );
  }

  /** Retire la déclaration du cache. */
  protected retirerDeclaration(declaration: DeclarationFDOBrisPorte): void {
    queryClient.setQueryData<DeclarationFDOBrisPorte[]>(
      APIDeclarationManager.CLEF_CACHE,
      (declarations = []) =>
        declarations.filter((d) => d.id !== declaration.id),
    );
  }

  async getListe(): Promise<DeclarationFDOBrisPorte[]> {
    return this.recupererDeclarations();
  }

  async aDeclaration(id: string): Promise<boolean> {
    return (await this.recupererDeclarations()).some(
      (declaration) => declaration.id == id,
    );
  }

  async getDeclaration(
    id: string,
  ): Promise<DeclarationFDOBrisPorte | undefined> {
    return (await this.recupererDeclarations()).find(
      (declaration) => declaration.id == id,
    );
  }

  async nouvelleDeclaration(): Promise<DeclarationFDOBrisPorte> {
    // Appel à la route API `api_agent_fdo_bris_porte_initier` :
    const response = await fetch(`/api/agent/fdo/bris-de-porte/initier`, {
      method: "PUT",
      headers: {
        Accept: "application/json",
      },
    });

    if (response.ok) {
      const declaration = plainToInstance(
        DeclarationFDOBrisPorte,
        await response.json(),
      );
      // S'assurer que la liste est chargée avant d'y ajouter le nouveau brouillon
      await this.recupererDeclarations();
      this.enregistrerDeclaration(declaration);

      return declaration;
    }

    throw new Error("Erreur lors de la création d'un nouveau brouillon");
  }

  mettreAJour(
    declaration: DeclarationFDOBrisPorte,
    miseAJour?: Partial<DeclarationFDOBrisPorte>,
  ): void {
    if (!!miseAJour) {
      this.enregistrerDeclaration(
        plainToInstance(
          DeclarationFDOBrisPorte,
          merge.withOptions(
            { mergeArrays: false },
            instanceToPlain(declaration),
            miseAJour,
          ),
        ),
      );
    }
  }

  async enregistrer(
    declaration: DeclarationFDOBrisPorte,
    miseAJour: Partial<DeclarationFDOBrisPorte>,
  ): Promise<void> {
    // Appel à la route API `api_agent_fdo_bris_porte_editer` :
    const response = await fetch(
      `/api/agent/fdo/bris-de-porte/${declaration.id}/editer`,
      {
        method: "PATCH",
        headers: {
          Accept: "application/json",
        },
        body: JSON.stringify({
          ...miseAJour,
          ...(miseAJour.coordonneesRequerant?.civilite
            ? {
                coordonneesRequerant: {
                  ...miseAJour.coordonneesRequerant,
                  civilite: miseAJour.coordonneesRequerant.civilite.id,
                },
              }
            : {}),
          ...(miseAJour.dateOperation
            ? {
                dateOperation: miseAJour.dateOperation
                  ?.toISOString()
                  .split("T")
                  .at(0),
              }
            : {}),
        }),
      },
    );

    if (response.ok) {
      this.enregistrerDeclaration(
        plainToInstance(DeclarationFDOBrisPorte, await response.json()),
      );
    }
  }

  async supprimer(declaration: DeclarationFDOBrisPorte): Promise<void> {
    // Appel à la route API `api_agent_fdo_bris_porte_supprimer` :
    const response = await fetch(
      `/api/agent/fdo/bris-de-porte/${declaration.id}/supprimer`,
      {
        method: "DELETE",
        headers: {
          "Content-type": "application/json",
          Accept: "application/json",
        },
      },
    );

    if (response.ok) {
      this.retirerDeclaration(declaration);
    }
  }

  async soumettre(declaration: DeclarationFDOBrisPorte): Promise<void> {
    // Appel à la route API `api_agent_fdo_bris_porte_soumettre` :
    const response = await fetch(
      `/api/agent/fdo/bris-de-porte/${declaration.id}/soumettre`,
      {
        method: "POST",
        headers: {
          "Content-type": "application/json",
          Accept: "application/json",
        },
      },
    );

    if (response.ok) {
      this.enregistrerDeclaration(
        plainToInstance(DeclarationFDOBrisPorte, await response.json()),
      );
    }
  }

  async invalider(): Promise<void> {
    await queryClient.invalidateQueries({
      queryKey: APIDeclarationManager.CLEF_CACHE,
    });
  }
}
