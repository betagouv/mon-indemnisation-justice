import { DeclarationErreurOperationnelle } from "@/apps/agent/fdo/models/DeclarationErreurOperationnelle.ts";
import { inject, ServiceIdentifier } from "inversify";
import { instanceToPlain, plainToInstance } from "class-transformer";
import * as Sentry from "@sentry/browser";
import { merge } from "ts-deepmerge";
import { read } from "fs";
import { AgentManagerInterface } from "@/common/services/agent/agent.ts";

export interface DeclarationManagerInterface {
  getListe(): Promise<DeclarationErreurOperationnelle[]>;

  aDeclaration(reference: string): Promise<boolean>;

  getDeclaration(
    reference: string,
  ): Promise<DeclarationErreurOperationnelle | undefined>;

  nouvelleDeclaration(): Promise<DeclarationErreurOperationnelle>;

  enregistrer(
    declaration: DeclarationErreurOperationnelle,
    miseAJour?: any,
  ): void | Promise<void>;

  soumettre(declaration: DeclarationErreurOperationnelle): Promise<void>;

  supprimer(declaration: DeclarationErreurOperationnelle): void;
}

export namespace DeclarationManagerInterface {
  export const $: ServiceIdentifier<DeclarationManagerInterface> = Symbol(
    "DeclarationManagerInterface",
  );
}

export class APIDeclarationManager implements DeclarationManagerInterface {
  static CLEF_STOCKAGE = "_declarations";

  protected _declarations: DeclarationErreurOperationnelle[];

  protected async chargerListeDeclarations(): Promise<void> {
    if (!this._declarations) {
      this._declarations = (
        await Promise.all([
          this.chargerBrouillons(),
          this.chargerDeclarations(),
        ])
      ).reduce((cum, val) => cum.concat(...(val ?? [])), []);
    }
  }

  protected async chargerBrouillons(): Promise<
    DeclarationErreurOperationnelle[]
  > {
    if (
      typeof localStorage.getItem(APIDeclarationManager.CLEF_STOCKAGE) ===
      "string"
    ) {
      return Promise.resolve(
        plainToInstance(
          DeclarationErreurOperationnelle,
          JSON.parse(
            localStorage.getItem(APIDeclarationManager.CLEF_STOCKAGE) as string,
          ) as any[],
        ),
      );
    }

    return Promise.resolve([]);
  }
  protected async chargerDeclarations(): Promise<
    DeclarationErreurOperationnelle[]
  > {
    const response = await fetch(
      "/api/agent/fdo/erreur-operationnelle/mes-declarations",
      {
        method: "GET",
        headers: {
          Accept: "application/json",
        },
      },
    );

    return plainToInstance(
      DeclarationErreurOperationnelle,
      (await response.json()) as any[],
    );
  }

  async getListe(): Promise<DeclarationErreurOperationnelle[]> {
    await this.chargerListeDeclarations();
    return this._declarations.sort(
      (a, b) => a.dateCreation.getTime() - b.dateCreation.getTime(),
    );
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
  ): Promise<DeclarationErreurOperationnelle | undefined> {
    await this.chargerListeDeclarations();

    return Promise.resolve(
      this._declarations.find(
        (declaration) => declaration.reference == reference,
      ),
    );
  }

  async nouvelleDeclaration(): Promise<DeclarationErreurOperationnelle> {
    await this.chargerListeDeclarations();
    this._declarations.push(new DeclarationErreurOperationnelle());

    localStorage.setItem(
      APIDeclarationManager.CLEF_STOCKAGE,
      JSON.stringify(instanceToPlain(this._declarations)),
    );

    return this._declarations.at(-1) as DeclarationErreurOperationnelle;
  }

  enregistrer(
    declaration: DeclarationErreurOperationnelle,
    miseAJour?: any,
  ): Promise<void> {
    this._declarations = this._declarations.map((d) => {
      if (declaration.dateCreation.getTime() === d.dateCreation.getTime()) {
        return miseAJour
          ? plainToInstance(
              DeclarationErreurOperationnelle,
              merge.withOptions(
                { mergeArrays: false },
                instanceToPlain(declaration),
                miseAJour,
              ),
            )
          : declaration;
      }

      return d;
    });

    localStorage.setItem(
      APIDeclarationManager.CLEF_STOCKAGE,
      JSON.stringify(
        instanceToPlain(this._declarations.filter((d) => d.estBrouillon())),
      ),
    );

    return Promise.resolve(undefined);
  }

  ajouter(declaration: DeclarationErreurOperationnelle): void {
    this._declarations = this._declarations
      .filter((d) => d.reference !== declaration.reference)
      .concat(declaration);
  }

  supprimer(declaration: DeclarationErreurOperationnelle): void {
    this._declarations = this._declarations.filter(
      (d) => d.reference !== declaration.reference,
    );

    localStorage.setItem(
      APIDeclarationManager.CLEF_STOCKAGE,
      JSON.stringify(
        instanceToPlain(this._declarations.filter((d) => d.estBrouillon())),
      ),
    );
  }

  async soumettre(declaration: DeclarationErreurOperationnelle): Promise<void> {
    // Appel API à `api_agent_fdo_erreur_operationnelle_declarer` :
    const response = await fetch(
      "/api/agent/fdo/erreur-operationnelle/declarer",
      {
        method: "PUT",
        headers: {
          "Content-type": "application/json",
          Accept: "application/json",
        },
        body: JSON.stringify(
          instanceToPlain(
            this._declarations.find(
              (d) => d.reference === declaration.reference,
            ),
          ),
        ),
      },
    );
    if (response.ok) {
      const declarationSauvegardee = plainToInstance(
        DeclarationErreurOperationnelle,
        await response.json(),
      );

      if (declarationSauvegardee) {
        this.ajouter(declarationSauvegardee);
        this.supprimer(declaration);
      }
    }
  }
}
