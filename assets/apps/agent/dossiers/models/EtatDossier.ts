import {plainToInstance} from "class-transformer";

export class EtatDossier {
    public readonly id: string;
    public readonly slug: string;
    public readonly libelle: string;

    public static catalog: EtatDossier[]

    public static resoudre(slug: string): null|EtatDossier {
        return this.catalog.find((e) => e.slug == slug) ?? null;
    }

    public static charger(data: any): void {
        const instance = plainToInstance(EtatDossier, data)
        this.catalog = instance instanceof EtatDossier ? [instance] : instance as EtatDossier[]
        console.log(this.catalog.map((e) => e.libelle));
    }
}