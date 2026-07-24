import "reflect-metadata";
import { instanceToPlain, plainToClassFromExist, plainToInstance } from "class-transformer";
import { injectable, ServiceIdentifier } from "inversify";
import { TestEligibilite } from "@/apps/public/models/TestEligibilite";
import { ActionContentieuse } from "@/apps/public/components/types";

// Source - https://stackoverflow.com/a/61132308
// Posted by Terry, modified by community. See post 'Timeline' for change history
// Retrieved 2026-03-09, License - CC BY-SA 4.0
export type DeepPartial<T> = T extends object
  ? {
      [P in keyof T]?: DeepPartial<T[P]>;
    }
  : T;

export interface TestEligibiliteManagerInterface {
  get(): TestEligibilite | undefined;
  creer(): TestEligibilite;
  modifier(modifications: DeepPartial<TestEligibilite>): void;
  effacer(): void;
  soumettre(): Promise<void>;
}

export namespace TestEligibiliteManagerInterface {
  export const $: ServiceIdentifier<TestEligibiliteManagerInterface> = Symbol(
    "TestEligibiliteManagerInterface",
  );
}

export class InMemoryTestEligibiliteManager implements TestEligibiliteManagerInterface {
  private test?: TestEligibilite;

  get(): TestEligibilite | undefined {
    return this.test;
  }

  creer(): TestEligibilite {
    this.test = new TestEligibilite();
    return this.test;
  }

  modifier(modifications: DeepPartial<TestEligibilite>): void {
    if (!this.test) {
      throw new Error("Aucun test d'éligibilité en cours");
    }
    this.test = plainToClassFromExist(this.test, modifications);
  }

  effacer(): void {
    this.test = undefined;
  }

  async soumettre(): Promise<void> {
    if (!this.test) {
      throw new Error("Aucun test d'éligibilité en cours");
    }
    return new Promise<void>((res) => setTimeout(res, Math.random() * 500));
  }
}

type StockageTestEligibilite = {
  data: Record<string, unknown>;
  expiresAt: string;
};

const CLEF_STOCKAGE = "dys_test_eligibilite";
const TTL_MS = 14 * 24 * 60 * 60 * 1000;

@injectable()
export class LocalStorageTestEligibiliteManager implements TestEligibiliteManagerInterface {
  private test?: TestEligibilite;

  get(): TestEligibilite | undefined {
    if (this.test) return this.test;

    try {
      const raw = localStorage.getItem(CLEF_STOCKAGE);
      if (!raw) return undefined;

      const stockage: StockageTestEligibilite = JSON.parse(raw);
      if (new Date() > new Date(stockage.expiresAt)) {
        localStorage.removeItem(CLEF_STOCKAGE);
        return undefined;
      }

      this.test = plainToInstance(TestEligibilite, stockage.data);
      return this.test;
    } catch {
      this.effacer();
      return undefined;
    }
  }

  creer(): TestEligibilite {
    this.test = new TestEligibilite();
    this.sauvegarder();
    return this.test;
  }

  modifier(modifications: DeepPartial<TestEligibilite>): void {
    if (!this.test) {
      throw new Error("Aucun test d'éligibilité en cours");
    }
    this.test = plainToClassFromExist(this.test, modifications);
    this.sauvegarder();
  }

  effacer(): void {
    this.test = undefined;
    localStorage.removeItem(CLEF_STOCKAGE);
  }

  async soumettre(): Promise<void> {
    if (!this.test) {
      throw new Error("Aucun test d'éligibilité en cours");
    }

    const reponse = await fetch("/api/public/dysfonctionnement/test-eligibilite", {
      method: "PUT",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        procedureTerminee: this.test.procedureTerminee,
        dateDecision: this.test.dateDecision?.toISOString().split("T")[0],
        aUneActionContentieuse: this.test.actionContentieuse === ActionContentieuse.Oui,
        typesDecision: this.test.typeDecision,
        piecesProcedure: this.test.piecesProc,
        preuvesDiligences: this.test.preuvesDiligences,
      }),
    });

    if (!reponse.ok) {
      throw new Error("Erreur lors de la soumission du test d'éligibilité");
    }
  }

  private sauvegarder(): void {
    const stockage: StockageTestEligibilite = {
      data: instanceToPlain(this.test) as Record<string, unknown>,
      expiresAt: new Date(Date.now() + TTL_MS).toISOString(),
    };
    localStorage.setItem(CLEF_STOCKAGE, JSON.stringify(stockage));
  }
}
