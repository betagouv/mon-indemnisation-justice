import { DeclarationFDOBrisPorte } from "@/apps/agent/fdo/models/DeclarationFDOBrisPorte.ts";
import { inject, ServiceIdentifier } from "inversify";
import { instanceToPlain, plainToInstance } from "class-transformer";
//import * as Sentry from "@sentry/browser";
import { merge } from "ts-deepmerge";
import { AgentManagerInterface } from "@/common/services/agent/agent.ts";

export interface DeclarationManagerInterface {
  getListe(): Promise<DeclarationFDOBrisPorte[]>;

  aDeclaration(reference: string): Promise<boolean>;

  getDeclaration(
    reference: string,
  ): Promise<DeclarationFDOBrisPorte | undefined>;

  nouvelleDeclaration(): Promise<DeclarationFDOBrisPorte>;

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
}

export namespace DeclarationManagerInterface {
  export const $: ServiceIdentifier<DeclarationManagerInterface> = Symbol(
    "DeclarationManagerInterface",
  );
}

export class APIDeclarationManager implements DeclarationManagerInterface {
  static CLEF_STOCKAGE = "_declarations";

  protected _declarations: DeclarationFDOBrisPorte[];

  public constructor(
    @inject(AgentManagerInterface.$)
    protected readonly agentManager: AgentManagerInterface,
  ) {}

  protected async chargerListeDeclarations(): Promise<void> {
    const { agent } = await this.agentManager.moi();
    // Suppression des brouillons non individuels
    localStorage.removeItem(APIDeclarationManager.CLEF_STOCKAGE);
    localStorage.removeItem(
      `${APIDeclarationManager.CLEF_STOCKAGE}_${agent.id}`,
    );

    if (!this._declarations) {
      const response = await fetch(
        "/api/agent/fdo/bris-de-porte/mes-declarations",
        {
          method: "GET",
          headers: {
            Accept: "application/json",
          },
        },
      );

      this._declarations = plainToInstance(
        DeclarationFDOBrisPorte,
        (await response.json()) as any[],
      ).sort((a, b) => a.dateCreation.getTime() - b.dateCreation.getTime());
    }
  }

  async getListe(): Promise<DeclarationFDOBrisPorte[]> {
    await this.chargerListeDeclarations();
    return this._declarations;
  }

  async aDeclaration(reference: string): Promise<boolean> {
    await this.chargerListeDeclarations();

    return Promise.resolve(
      this._declarations.some(
        (declaration) => declaration.reference == reference,
      ),
    );
  }

  async getDeclaration(
    reference: string,
  ): Promise<DeclarationFDOBrisPorte | undefined> {
    await this.chargerListeDeclarations();

    return Promise.resolve(
      this._declarations.find(
        (declaration) => declaration.reference == reference,
      ),
    );
  }

  async nouvelleDeclaration(): Promise<DeclarationFDOBrisPorte> {
    await this.chargerListeDeclarations();

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
      this._declarations.push(declaration);

      return declaration;
    }

    throw new Error("Erreur lors de la création d'un nouveau brouillon");
  }

  protected setDeclaration(
    reference: string,
    declaration: DeclarationFDOBrisPorte,
  ): void {
    this._declarations = this._declarations.map((d: DeclarationFDOBrisPorte) =>
      d.id === reference ? declaration : d,
    );
  }

  mettreAJour(
    declaration: DeclarationFDOBrisPorte,
    miseAJour?: Partial<DeclarationFDOBrisPorte> | DeclarationFDOBrisPorte,
  ): void {
    this.setDeclaration(
      declaration.reference,
      // Si la mise à jour concerne une déclaration complète...
      miseAJour instanceof DeclarationFDOBrisPorte
        ? // ... alors, on la remplace dans la liste
          miseAJour
        : // ... sinon on fusionne la mise à jour partielle avec les valeurs déjà existantes
          plainToInstance(
            DeclarationFDOBrisPorte,
            merge.withOptions(
              { mergeArrays: false },
              instanceToPlain(declaration),
              miseAJour as any,
            ),
          ),
    );
  }

  async enregistrer(
    declaration: DeclarationFDOBrisPorte,
    miseAJour: Partial<DeclarationFDOBrisPorte>,
  ): Promise<void> {
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
      this.setDeclaration(
        declaration.reference,
        plainToInstance(DeclarationFDOBrisPorte, await response.json()),
      );
    }
  }

  ajouter(declaration: DeclarationFDOBrisPorte): void {
    this._declarations = this._declarations
      .filter((d) => d.reference !== declaration.reference)
      .concat(declaration);
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
      this._declarations = this._declarations.filter(
        (d) => d.reference !== declaration.reference,
      );
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
      this._declarations = this._declarations.filter(
        (d) => d.reference !== declaration.reference,
      );
      this.ajouter(
        plainToInstance(DeclarationFDOBrisPorte, await response.json()),
      );
    }
  }
}
