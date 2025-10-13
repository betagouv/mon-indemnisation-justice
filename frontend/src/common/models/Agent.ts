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

export type TypeAdministration = "MJ" | "GN" | "PN";

export class Administration {
  public static MJ: Administration = new Administration(
    "MJ",
    "Ministère de la Justice",
  );
  public static GN: Administration = new Administration(
    "GN",
    "Gendarmerie Nationale",
    true,
  );
  public static PN: Administration = new Administration(
    "PN",
    "Police Nationale",
    true,
  );

  protected constructor(
    public type: TypeAdministration,
    public libelle: string,
    public readonly estLibelleFeminin: boolean = false,
    public readonly roles: string[] = [],
  ) {}

  public static liste(): Administration[] {
    return [this.MJ, this.GN, this.PN];
  }

  public static pourType(type: TypeAdministration): Administration {
    return this.liste().find(
      (a: Administration) => a.type == type,
    ) as Administration;
  }
}

export class Agent {
  public id: number;
  public prenom: string;
  public nom: string;
  public courriel: string;

  @Transform(({ value }: { value: string[] }) => new Set(value))
  protected roles: Set<AgentPermission>;
  @Transform(({ value }: { value: TypeAdministration }) =>
    Administration.pourType(value),
  )
  public administration: Administration;
  // TODO généraliser https://stackoverflow.com/a/61732579/4558679
  @Transform(({ value }) => new Date(value))
  public readonly datePremiereConnexion: Date;

  public estAttributeur(): boolean {
    return this.roles.has(AgentPermissionType.ATTRIBUTEUR);
  }

  public estValidateur(): boolean {
    return this.roles.has(AgentPermissionType.VALIDATEUR);
  }

  public estRedacteur(): boolean {
    return this.roles.has(AgentPermissionType.REDACTEUR);
  }

  public estLiaisonBudget(): boolean {
    return this.roles.has(AgentPermissionType.LIAISON_BUDGET);
  }

  public estBetagouv(): boolean {
    return this.roles.has(AgentPermissionType.BETAGOUV);
  }

  public instruit(dossier: BaseDossier): boolean {
    return this.estRedacteur() && this.equals(dossier.redacteur);
  }

  nomComplet(): string {
    return `${this.prenom} ${this.nom.toUpperCase()}`;
  }

  equals(redacteur: Redacteur | null): boolean {
    return redacteur?.id == this.id;
  }
}
