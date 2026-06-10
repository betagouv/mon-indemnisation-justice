import { plainToClassFromExist } from "class-transformer";
import { ServiceIdentifier } from "inversify";
import { TestEligibilite } from "@/apps/public/models/TestEligibilite";

// Source - https://stackoverflow.com/a/61132308
// Posted by Terry, modified by community. See post 'Timeline' for change history
// Retrieved 2026-03-09, License - CC BY-SA 4.0
export type DeepPartial<T> = T extends object
  ? {
      [P in keyof T]?: DeepPartial<T[P]>;
    }
  : T;

export interface TestEligibiliteManagerInterface {
  creer(): TestEligibilite;
  modifier(modifications: DeepPartial<TestEligibilite>): void;
  soumettre(): Promise<void>;
}

export namespace TestEligibiliteManagerInterface {
  export const $: ServiceIdentifier<TestEligibiliteManagerInterface> = Symbol(
    "TestEligibiliteManagerInterface",
  );
}

export class InMemoryTestEligibiliteManager implements TestEligibiliteManagerInterface {
  private test?: TestEligibilite;

  creer(): TestEligibilite {
    this.test = new TestEligibilite();
    return this.test;
  }

  modifier(modifications: DeepPartial<TestEligibilite>): void {
    if (!this.test) {
      throw new Error("Aucun test d'éligibilité en cours");
    }
    plainToClassFromExist(this.test, modifications);
  }

  async soumettre(): Promise<void> {
    if (!this.test) {
      throw new Error("Aucun test d'éligibilité en cours");
    }
  }
}
