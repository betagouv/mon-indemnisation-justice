import { BaseDossier } from "@/common/models/Dossier";
import { Redacteur } from "@/common/models/Redacteur";
import { Expose, Transform } from "class-transformer";

export type TypeRoleAgent =
  | "ROLE_AGENT_DOSSIER"
  | "ROLE_AGENT_REDACTEUR"
  | "ROLE_AGENT_GESTION_PERSONNEL"
  | "ROLE_AGENT_ATTRIBUTEUR"
  | "ROLE_AGENT_VALIDATEUR"
  | "ROLE_AGENT_LIAISON_BUDGET"
  | "ROLE_AGENT_BETAGOUV"
  | "ROLE_AGENT_FORCES_DE_L_ORDRE";

export class RoleAgent {
  static readonly DOSSIER = new RoleAgent(
    "ROLE_AGENT_DOSSIER",
    "Consultation des dossiers",
    "Peut rechercher et consulter les dossiers",
  );
  static readonly REDACTEUR = new RoleAgent(
    "ROLE_AGENT_REDACTEUR",
    "Rédacteur",
    "Instruit les dossiers d'indemnisation",
  );
  static readonly GESTION_PERSONNEL = new RoleAgent(
    "ROLE_AGENT_GESTION_PERSONNEL",
    "Gestionnaire d'agent",
    "Définit les permissions accordées aux agents",
  );
  static readonly ATTRIBUTEUR = new RoleAgent(
    "ROLE_AGENT_ATTRIBUTEUR",
    "Attributeur",
    "Attribue les dossiers aux rédacteurs",
  );
  static readonly VALIDATEUR = new RoleAgent(
    "ROLE_AGENT_VALIDATEUR",
    "Validateur",
    "Valide les décisions et signe les documents",
  );
  static readonly LIAISON_BUDGET = new RoleAgent(
    "ROLE_AGENT_LIAISON_BUDGET",
    "Liaison budget",
    "Assure la liaison avec le bureau du budget",
  );
  static readonly BETAGOUV = new RoleAgent(
    "ROLE_AGENT_BETAGOUV",
    "Membre Beta.gouv",
    "Membre de l'équipe Mon Indemnisation Justice",
  );
  static readonly FORCES_DE_L_ORDRE = new RoleAgent(
    "ROLE_AGENT_FORCES_DE_L_ORDRE",
    "Agent des forces de l'ordre",
    "Déclare des bris de porte",
  );

  protected constructor(
    readonly type: TypeRoleAgent,
    readonly libelle: string,
    readonly description: string,
  ) {}

  static liste(): RoleAgent[] {
    return [
      this.DOSSIER,
      this.REDACTEUR,
      this.GESTION_PERSONNEL,
      this.ATTRIBUTEUR,
      this.VALIDATEUR,
      this.LIAISON_BUDGET,
      this.BETAGOUV,
    ];
  }

  static pourType(type?: TypeRoleAgent): RoleAgent | undefined {
    return type
      ? (this.liste().find((r) => r.type === type) as RoleAgent)
      : undefined;
  }
}

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

  @Expose({ name: "roles" })
  @Transform(({ value }: { value: string[] }) => {
    return value
      .map((r) => RoleAgent.pourType(r as TypeRoleAgent))
      .filter((r) => !!r);
  })
  protected _roles: RoleAgent[] = [];
  @Transform(({ value }: { value: string }) =>
    Administration.pourType(value as TypeAdministration),
  )
  public administration: Administration;
  // TODO généraliser https://stackoverflow.com/a/61732579/4558679
  @Transform(({ value }) => new Date(value))
  public readonly dateCreation: Date;

  get roles(): RoleAgent[] {
    return this._roles;
  }

  aRole(role: RoleAgent): boolean {
    return this._roles.includes(role);
  }

  aAuMoinsUnRole(...roles: RoleAgent[]): boolean {
    return this._roles.some((r) => roles.includes(r));
  }

  definirRole(role: RoleAgent, aRole: boolean): void {
    if (aRole) {
      if (!this.aRole(role)) {
        this.ajouterRole(role);
      }
    } else {
      this.retirerRole(role);
    }
  }

  ajouterRole(role: RoleAgent): void {
    this._roles.push(role);
  }

  retirerRole(role: RoleAgent): void {
    this._roles = this._roles.filter((r) => r.type == role.type);
  }

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

  public estBetagouv(): boolean {
    return this.aRole(RoleAgent.BETAGOUV);
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
