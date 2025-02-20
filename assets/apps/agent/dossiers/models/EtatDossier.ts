import {plainToInstance} from "class-transformer";


export enum EtatDossierType {
    DOSSIER_INITIE = 'DOSSIER_INITIE',
    DOSSIER_DEPOSE = 'DOSSIER_DEPOSE',
    DOSSIER_ACCEPTE = 'DOSSIER_ACCEPTE',
    DOSSIER_REJETE = 'DOSSIER_REJETE',
}

export class EtatDossier {
    public readonly id: string;
    public readonly slug: string;
    public readonly libelle: string;
    public readonly estDisponibleRecherche: boolean;

    protected constructor(id: string, slug: string, libelle: string, estDisponibleRecherche: boolean = true) {
        this.id = id;
        this.slug = slug;
        this.libelle = libelle;
        this.estDisponibleRecherche = estDisponibleRecherche;
    }

    protected static _catalog: Map<EtatDossierType, EtatDossier> = new Map(
        [
            [EtatDossierType.DOSSIER_INITIE, new EtatDossier(EtatDossierType.DOSSIER_INITIE, 'a-finaliser', "À finaliser", false)],
            [EtatDossierType.DOSSIER_DEPOSE, new EtatDossier(EtatDossierType.DOSSIER_DEPOSE, 'a-instruire', "À instruire")],
            [EtatDossierType.DOSSIER_ACCEPTE, new EtatDossier(EtatDossierType.DOSSIER_ACCEPTE, 'a-valider-acc', "À valider (accepté)")],
            [EtatDossierType.DOSSIER_REJETE, new EtatDossier(EtatDossierType.DOSSIER_REJETE, 'a-valider-rej', "À valider (rejeté)")],
        ]
    );

    public static get liste(): EtatDossier[] {
        return EtatDossier._catalog.values().toArray()
    }

    public static resoudreParSlug(slug: string): null | EtatDossier {
        return this._catalog.values().find((e) => e.slug == slug) ?? null;
    }

    public static resoudre(id: string): null | EtatDossier {
        return this._catalog.get(id as EtatDossierType) ?? null;
    }

    public static charger(data: []): void {
    }
}
