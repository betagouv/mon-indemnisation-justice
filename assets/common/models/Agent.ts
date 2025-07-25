import { BaseDossier } from "@/apps/agent/dossiers/models/Dossier";
import { Redacteur } from "@/apps/agent/dossiers/models/Redacteur";
import { Transform } from "class-transformer";

export enum AgentPermissionType {
  REDACTEUR = "REDACTEUR",
  ATTRIBUTEUR = "ATTRIBUTEUR",
  VALIDATEUR = "VALIDATEUR",
  LIAISON_BUDGET = "LIAISON_BUDGET",
}

export type AgentPermission =
  | AgentPermissionType.REDACTEUR
  | AgentPermissionType.ATTRIBUTEUR
  | AgentPermissionType.VALIDATEUR
  | AgentPermissionType.LIAISON_BUDGET;

export class Agent {
  public id: number;

  @Transform(({ value }: { value: string[] }) => new Set(value))
  protected permissions: Set<AgentPermission>;

  public estAttributeur(): boolean {
    return this.permissions.has(AgentPermissionType.ATTRIBUTEUR);
  }

  public estValidateur(): boolean {
    return this.permissions.has(AgentPermissionType.VALIDATEUR);
  }

  public estRedacteur(): boolean {
    return this.permissions.has(AgentPermissionType.REDACTEUR);
  }

  public estLiaisonBudget(): boolean {
    return this.permissions.has(AgentPermissionType.LIAISON_BUDGET);
  }

  public instruit(dossier: BaseDossier): boolean {
    return this.equals(dossier.redacteur);
  }

  equals(redacteur: Redacteur | null): boolean {
    return redacteur?.id == this.id;
  }
}
