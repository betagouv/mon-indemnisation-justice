import { Agent } from "@/common/models";
import { AffectationAgentFDO } from "@fdo/modeles/AffectationAgentFDO.ts";
import { plainToInstance, Transform } from "class-transformer";

export class AgentFDO extends Agent {
  @Transform(({ value }) => {
    if (Array.isArray(value)) {
      return plainToInstance(AffectationAgentFDO, value);
    }

    if (value === false) {
      return false;
    }

    return [];
  })
  public affectations: AffectationAgentFDO[] | false;

  public estAffecte(): boolean {
    return !!this.getAffectationActive();
  }

  public getAffectationActive(): AffectationAgentFDO | undefined {
    if (this.affectations === false) {
      return undefined;
    }

    return this.affectations.find(
      (affectation: AffectationAgentFDO) => affectation.estActive,
    );
  }
}
