import {Adresse, Document, EtatDossier, Redacteur, Requerant,} from "@/apps/agent/dossiers/models";
import {Expose, Transform, Type} from "class-transformer";
import {action, computed, makeObservable, observable} from "mobx";

export abstract class BaseDossier {
    public readonly id: number;
    public readonly reference: string;

    @Transform(({value}: { value: string }) => EtatDossier.resoudre(value))
    public etat: EtatDossier = null;
    protected _dateDepot: Date | null;
    protected _attributaire: Redacteur | null;

    @Expose()
    get dateDepot(): null | Date {
        return this._dateDepot;
    }

    set dateDepot(value: Date | number) {
        this._dateDepot = typeof value === "number" ? new Date(value) : value;
    }

    @Expose()
    get attributaire(): Redacteur | null {
        return this._attributaire;
    }

    set attributaire(value: Redacteur | number | null) {
        this._attributaire =
            typeof value == "number" ? Redacteur.resoudre(value) : value;
    }

    get enAttenteDecision(): boolean {
        return this.etat == EtatDossier.A_INSTRUIRE;
    }

    changerEtat(etat: EtatDossier): void {
        this.etat = etat instanceof EtatDossier ? etat : EtatDossier.resoudre(etat);
    }
}

export class DossierApercu extends BaseDossier {
    public readonly requerant: string; // Prénom NOM
    public readonly adresse: string;
}

export class DossierDetail extends BaseDossier {
    @Expose()
    @Type(() => Requerant)
    public readonly requerant: Requerant;

    @Expose()
    @Transform(({value}: { value: number }) => Redacteur.resoudre(value))
    public redacteur: Redacteur | null = null;

    @Expose()
    @Type(() => Adresse)
    public readonly adresse: Adresse;

    @Transform(({value}) => {
        if (!value) {
            return null;
        }
        typeof value === "number" ? new Date(value) : value;
    })
    public dateOperation: Date | null;

    public estPorteBlindee: boolean | null;

    @Transform(({value}: { value: object }) => new Map(Object.entries(value)))
    public documents: Map<string, Document[]>;

    constructor() {
        super();
        makeObservable(this, {
            redacteur: observable,
            attribuer: action,
            enAttenteDecision: computed,
            etat: observable,
            changerEtat: action,
        });
    }

    attribuer(redacteur: Redacteur): void {
        this.redacteur = redacteur;
    }
}
