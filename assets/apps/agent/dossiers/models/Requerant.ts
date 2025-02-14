import {Expose} from "class-transformer";
import _ from "lodash";

export type Civilite = 'M' | 'MME';

export class Requerant {

    public readonly civilite: Civilite;
    public readonly nom: string;
    public readonly nomNaissance: string | null;
    public readonly prenoms: string[];
    protected _dateNaissance: Date | null;
    public readonly communeNaissance: string | null;
    public readonly paysNaissance : string | null;
    public readonly raisonSociale : string | null;
    public readonly siren : string | null;

    estFeminin(): boolean {
        return this.civilite == 'MME'
    }

    get prenom(): string {
        return this.prenoms.at(0)
    }

    @Expose()
    get dateNaissance(): null | Date {
        return this._dateNaissance
    }

    set dateNaissance(dateNaissance: Date | number) {
        this._dateNaissance = typeof dateNaissance === 'number' ? new Date(dateNaissance) : dateNaissance;
    }

    estPersonneMorale(): boolean {
        return this.raisonSociale !== null;
    }

    civiliteComplete(): string {
        return this.civilite === 'MME' ? 'Madame' : 'Monsieur'
    }


    nomSimple(): string {
        return `${this.prenom} ${this.nom}`
    }

    nomComplet(): string {
        return `${this.civiliteComplete()} ${this.nom} ` + (this.nomNaissance ? `, né${this.estFeminin() ? 'e' : ''} ${this.nomNaissance}` : '')
    }

}