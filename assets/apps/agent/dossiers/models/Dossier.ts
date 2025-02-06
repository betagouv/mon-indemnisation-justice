import {EtatDossier} from "@/apps/agent/dossiers/models/EtatDossier";
import {Redacteur} from "@/apps/agent/dossiers/models/Redacteur";
import {Expose} from "class-transformer";
import {number} from "prop-types";

export class Dossier {
    public readonly id: number;
    public readonly reference: string;
    public readonly etat: EtatDossier;
    public readonly requerant: string; // Prénom NOM
    public readonly adresse: string;
    protected _dateDepot: Date|null;
    private _attributaire: Redacteur|null;

    @Expose()
    get dateDepot(): null|Date
    {
        return this._dateDepot
    }

    set dateDepot(value: Date | number) {
        this._dateDepot = typeof value === 'number' ? new Date(value) : value;
    }


    @Expose()
    get attributaire(): Redacteur | null {
        return this._attributaire;
    }

    set attributaire(value: Redacteur | number | null) {
        this._attributaire = typeof value == 'number' ? Redacteur.resoudre(value) : value;
    }
}