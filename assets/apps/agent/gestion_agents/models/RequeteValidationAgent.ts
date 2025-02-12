import {Agent} from "@/apps/agent/gestion_agents/models/Agent.js";
import {Administration} from "@/apps/agent/gestion_agents/models/Administration.js";
import {action, computed, makeObservable, observable} from "mobx";
import {plainToInstance} from "class-transformer";

export class RequeteAgentValidation {
    public readonly agent: Agent;
    protected _administration: null | Administration;
    public roles: null | string[];

    constructor(agent: Agent) {
        this.agent = agent;
        this._administration = null
        this.roles = this.agent.roles.filter((role) => role !== 'ROLE_AGENT')
        makeObservable(this, {
            _administration: observable,
            administration: computed,
            roles: observable,
            reinitialiser: action,
            definirRole: action,
        } as any)
    }

    protected viderRoles(): void {
        // Vide la liste des rôles sans _muter_ la propriété roles
        this.roles.length = 0;
    }

    public reinitialiser(): void {
        this._administration = null;
        this.viderRoles();
    }

    aRole(role: string): boolean {
        return this.roles.includes(role);
    }

    public definirRole(role: string, estOctroye: boolean = true): void {
        if (estOctroye) {
            this.ajouterRole(role);
        } else {
            this.retirerRole(role);
        }
    }

    protected ajouterRole(role: string): void {
        if (!this.aRole(role)) {
            this.roles.push(role)
        }
    }

    protected retirerRole(role: string): void {
        // Suppression d'un élément de la liste de manière mutable
        this.roles.splice(this.roles.indexOf(role), 1)
    }

    get administration(): Administration | null {
        return this.agent.administration ?? this._administration;
    }

    set administration(administration: Administration | null) {
        this._administration = administration;
        this.viderRoles();
        // Si l'administration ne permet qu'un seul rôle, on l'ajoute automatiquement
        if (this._administration.roles.length == 1) {
            this.ajouterRole(this._administration.roles.at(0))
        }
    }

    public estValide(): boolean {
        return this.administration && (this.agent.roles.filter((role) => role !== 'ROLE_AGENT').length > 0 || this.roles.length > 0);
    }
}

export class RequeteAgentValidationListe {
    public validations: RequeteAgentValidation[] = [];

    constructor(agents: Agent[]) {
        makeObservable(this, {
            validations: observable,
            validationsValides: computed,
            ajouterAgentValidation: action,
        })
        this.validations = agents.map((agent) => new RequeteAgentValidation(agent));
    }

    ajouterAgentValidation(agent: Agent) {
        this.validations.push(new RequeteAgentValidation(agent))
    }

    get validationsValides(): RequeteAgentValidation[] {
        return this.validations.filter((v) => v.estValide());
    }

    async sauvegarder() {
        const response = await fetch(`${document.URL}.json`, {
            method: 'POST',
            body: JSON.stringify(
                Object.fromEntries(
                    this.validationsValides.map((validation) => [
                        validation.agent.id,
                        {
                            administration: validation.administration.id,
                            roles: validation.roles.concat('ROLE_AGENT')
                        }
                    ])
                )
            )
        });

        const { agents: _agts } = await response.json();

        const agents: Agent[] = plainToInstance(Agent, (_agts as any[]));

        this.validations = agents.map((agent) => new RequeteAgentValidation(agent));
    }
}