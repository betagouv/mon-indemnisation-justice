import { DeclarationErreurOperationnelle } from "@/apps/agent/fdo/models/DeclarationErreurOperationnelle.ts";
import { ServiceIdentifier } from "inversify";
import { instanceToPlain, plainToInstance } from "class-transformer";

export interface DeclarationManagerInterface {
  getListe(): Promise<DeclarationErreurOperationnelle[]>;

  aDeclaration(reference: string): Promise<boolean>;

  getDeclaration(
    reference: string,
  ): Promise<DeclarationErreurOperationnelle | undefined>;

  nouvelleDeclaration(): DeclarationErreurOperationnelle;

  enregistrer(
    declaration: DeclarationErreurOperationnelle,
  ): void | Promise<void>;

  soumettre(declaration: DeclarationErreurOperationnelle): Promise<void>;
}

export namespace DeclarationManagerInterface {
  export const $: ServiceIdentifier<DeclarationManagerInterface> = Symbol(
    "DeclarationManagerInterface",
  );
}

export class APIDeclarationManager implements DeclarationManagerInterface {
  static CLEF_STOCKAGE = "_declarations";

  protected _declarations: DeclarationErreurOperationnelle[];

  protected chargerListeDeclarations(): Promise<void> {
    if (!this._declarations) {
      if (
        typeof localStorage.getItem(APIDeclarationManager.CLEF_STOCKAGE) ===
        "string"
      ) {
        this._declarations = plainToInstance(
          DeclarationErreurOperationnelle,
          JSON.parse(
            localStorage.getItem(APIDeclarationManager.CLEF_STOCKAGE) as string,
          ) as any[],
        );
      } else {
        this._declarations = [];
      }
    }

    return Promise.resolve();
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

  nouvelleDeclaration(): DeclarationErreurOperationnelle {
    this._declarations.push(new DeclarationErreurOperationnelle());

    localStorage.setItem(
      APIDeclarationManager.CLEF_STOCKAGE,
      JSON.stringify(instanceToPlain(this._declarations)),
    );

    return this._declarations.at(-1) as DeclarationErreurOperationnelle;
  }

  enregistrer(declaration: DeclarationErreurOperationnelle): Promise<void> {
    this._declarations.forEach((d) =>
      declaration.reference === d.reference ? declaration : d,
    );

    localStorage.setItem(
      APIDeclarationManager.CLEF_STOCKAGE,
      JSON.stringify(instanceToPlain(this._declarations)),
    );

    return Promise.resolve(undefined);
  }

  soumettre(declaration: DeclarationErreurOperationnelle): Promise<void> {
    return Promise.resolve(undefined);
  }
}
