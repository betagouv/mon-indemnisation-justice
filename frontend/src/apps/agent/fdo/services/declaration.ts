import { DeclarationFDOBrisPorte } from "@/apps/agent/fdo/models/DeclarationFDOBrisPorte.ts";
import { inject, ServiceIdentifier } from "inversify";
import { instanceToPlain, plainToInstance } from "class-transformer";
import * as Sentry from "@sentry/browser";
import { merge } from "ts-deepmerge";
import { AgentManagerInterface } from "@/common/services/agent/agent.ts";

export interface DeclarationManagerInterface {
  getListe(): Promise<DeclarationFDOBrisPorte[]>;

  aDeclaration(reference: string): Promise<boolean>;

  getDeclaration(
    reference: string
  ): Promise<DeclarationFDOBrisPorte | undefined>;

  nouvelleDeclaration(): Promise<DeclarationFDOBrisPorte>;

  enregistrer(
    declaration: DeclarationFDOBrisPorte,
    miseAJour?: Partial<DeclarationFDOBrisPorte>
  ): void | Promise<void>;

  soumettre(declaration: DeclarationFDOBrisPorte): Promise<void>;

  supprimer(declaration: DeclarationFDOBrisPorte): void;
}

export namespace DeclarationManagerInterface {
  export const $: ServiceIdentifier<DeclarationManagerInterface> = Symbol(
    "DeclarationManagerInterface"
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
    if (!this._declarations) {
      this._declarations = (
        await Promise.all([
          this.chargerBrouillons(),
          this.chargerDeclarations()
        ])
      ).reduce((cum, val) => cum.concat(...(val ?? [])), []);
    }
  }

  protected async chargerBrouillons(): Promise<DeclarationFDOBrisPorte[]> {
    const { agent } = await this.agentManager.moi();
    // Suppression des brouillons non individuels
    localStorage.removeItem(APIDeclarationManager.CLEF_STOCKAGE);

    if (
      typeof localStorage.getItem(
        `${APIDeclarationManager.CLEF_STOCKAGE}_${agent.id}`,
      ) === "string"
    ) {
      return Promise.resolve(
        plainToInstance(
          DeclarationFDOBrisPorte,
          JSON.parse(
            localStorage.getItem(
              `${APIDeclarationManager.CLEF_STOCKAGE}_${agent.id}`,
            ) as string,
          ) as any[],
        ),
      );
    }

    return Promise.resolve([]);
  }

  protected async chargerDeclarations(): Promise<DeclarationFDOBrisPorte[]> {
    const response = await fetch(
      "/api/agent/fdo/erreur-operationnelle/mes-declarations",
      {
        method: "GET",
        headers: {
          Accept: "application/json"
        }
      }
    );

    return plainToInstance(
      DeclarationFDOBrisPorte,
      (await response.json()) as any[]
    );
  }

  async getListe(): Promise<DeclarationFDOBrisPorte[]> {
    await this.chargerListeDeclarations();
    return this._declarations.sort(
      (a, b) => a.dateCreation.getTime() - b.dateCreation.getTime()
    );
  }

  async aDeclaration(reference: string): Promise<boolean> {
    await this.chargerListeDeclarations();

    return Promise.resolve(
      this._declarations.some(
        (declaration) => declaration.reference == reference
      )
    );
  }

  async getDeclaration(
    reference: string
  ): Promise<DeclarationFDOBrisPorte | undefined> {
    await this.chargerListeDeclarations();

    return Promise.resolve(
      this._declarations.find(
        (declaration) => declaration.reference == reference
      )
    );
  }

  async nouvelleDeclaration(): Promise<DeclarationFDOBrisPorte> {
    await this.chargerListeDeclarations();
    const { agent } = await this.agentManager.moi();

    this._declarations.push(new DeclarationFDOBrisPorte());

    localStorage.setItem(
      `${APIDeclarationManager.CLEF_STOCKAGE}_${agent.id}`,
      JSON.stringify(instanceToPlain(this._declarations)),
    );

    return this._declarations.at(-1) as DeclarationFDOBrisPorte;
  }

  async enregistrer(
    declaration: DeclarationFDOBrisPorte,
    miseAJour?: any
  ): Promise<void> {
    this._declarations = this._declarations.map((d) => {
      if (declaration.dateCreation.getTime() === d.dateCreation.getTime()) {
        return miseAJour
          ? plainToInstance(
            DeclarationFDOBrisPorte,
            merge.withOptions(
              { mergeArrays: false },
              instanceToPlain(declaration),
              miseAJour
            )
          )
          : declaration;
      }

      return d;
    });

    const { agent } = await this.agentManager.moi();

    localStorage.setItem(
      `${APIDeclarationManager.CLEF_STOCKAGE}_${agent.id}`,
      JSON.stringify(
        instanceToPlain(this._declarations.filter((d) => d.estBrouillon()))
      )
    );

    return Promise.resolve(undefined);
  }

  ajouter(declaration: DeclarationFDOBrisPorte): void {
    this._declarations = this._declarations
      .filter((d) => d.reference !== declaration.reference)
      .concat(declaration);
  }

  supprimer(declaration: DeclarationFDOBrisPorte): void {
    this._declarations = this._declarations.filter(
      (d) => d.reference !== declaration.reference
    );

    localStorage.setItem(
      APIDeclarationManager.CLEF_STOCKAGE,
      JSON.stringify(
        instanceToPlain(this._declarations.filter((d) => d.estBrouillon()))
      )
    );
  }

  async soumettre(declaration: DeclarationFDOBrisPorte): Promise<void> {
    // Appel API à `api_agent_fdo_erreur_operationnelle_declarer` :
    const response = await fetch(
      "/api/agent/fdo/erreur-operationnelle/declarer",
      {
        method: "PUT",
        headers: {
          "Content-type": "application/json",
          Accept: "application/json"
        },
        body: JSON.stringify(
          instanceToPlain(
            this._declarations.find(
              (d) => d.reference === declaration.reference
            )
          )
        )
      }
    );
    if (response.ok) {
      const declarationSauvegardee = plainToInstance(
        DeclarationFDOBrisPorte,
        await response.json()
      );

      if (declarationSauvegardee) {
        this.ajouter(declarationSauvegardee);
        this.supprimer(declaration);
      }
    }
  }
}
