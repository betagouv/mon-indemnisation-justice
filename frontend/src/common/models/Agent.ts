import { BaseDossier } from "@/common/models/Dossier";
import { Redacteur } from "@/common/models/Redacteur";
import { Transform } from "class-transformer";

export enum AgentPermissionType {
  DOSSIER = "DOSSIER",
  REDACTEUR = "REDACTEUR",
  GESTION_PERSONNEL = "GESTION_PERSONNEL",
  ATTRIBUTEUR = "ATTRIBUTEUR",
  VALIDATEUR = "VALIDATEUR",
  LIAISON_BUDGET = "LIAISON_BUDGET",
  BETAGOUV = "BETAGOUV",
}

export type AgentPermission =
  | AgentPermissionType.REDACTEUR
  | AgentPermissionType.ATTRIBUTEUR
  | AgentPermissionType.VALIDATEUR
  | AgentPermissionType.LIAISON_BUDGET
  | AgentPermissionType.BETAGOUV;

export class Agent {
  public id: number;
  public readonly prenom: string;
  public readonly nom: string;

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

  public estBetagouv(): boolean {
    return this.permissions.has(AgentPermissionType.BETAGOUV);
  }

  public instruit(dossier: BaseDossier): boolean {
    return this.estRedacteur() && this.equals(dossier.redacteur);
  }

  equals(redacteur: Redacteur | null): boolean {
    return redacteur?.id == this.id;
  }
}
