import {Administration} from "@/apps/agent/gestion_agents/models/Administration.js";
// @ts-ignore
import { Expose } from "class-transformer";

export class Agent {
    public readonly id: number;
    public readonly nom: string;
    public readonly prenom: string;
    public readonly courriel: string;
    private _administration: null | Administration;
    public readonly roles: string[];
    private _datePremiereConnexion: Date;

    public nomComplet(): string {
        return `${this.prenom} ${this.nom.toUpperCase()}`;
    }


    get datePremiereConnexion(): Date {
        return this._datePremiereConnexion;
    }

    set datePremiereConnexion(value: Date | number) {
        this._datePremiereConnexion = typeof value === 'number' ? new Date(value) : value;
    }

    @Expose()
    get administration(): Administration | null {
        return this._administration;
    }

    @Expose()
    set administration(value: Administration | string | null) {
        this._administration = typeof value === 'string' ? Administration.resoudre(value) : value;
    }
}