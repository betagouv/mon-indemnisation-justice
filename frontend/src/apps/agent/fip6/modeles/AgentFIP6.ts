import { Agent, BaseDossier, Redacteur } from "@common/models";
import { RoleAgent } from "@common/models/Agent.ts";

export class AgentFIP6 extends Agent {
  public estAttributeur(): boolean {
    return this.aRole(RoleAgent.ATTRIBUTEUR);
  }

  public estValidateur(): boolean {
    return this.aRole(RoleAgent.VALIDATEUR);
  }

  public estRedacteur(): boolean {
    return this.aRole(RoleAgent.REDACTEUR);
  }

  public estLiaisonBudget(): boolean {
    return this.aRole(RoleAgent.LIAISON_BUDGET);
  }

  public estGestionnairePersonnel(): boolean {
    return this.aRole(RoleAgent.GESTION_PERSONNEL);
  }

  public instruit(dossier: BaseDossier): boolean {
    return this.estRedacteur() && this.equals(dossier.redacteur);
  }

  equals(redacteur?: Redacteur): boolean {
    return redacteur?.id == this.id;
  }
}
