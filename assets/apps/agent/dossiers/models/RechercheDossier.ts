import {EtatDossier} from "@/apps/agent/dossiers/models/EtatDossier";
import {action, computed, makeObservable, observable} from "mobx";
import {Redacteur} from "./Redacteur.ts";

/**
 * Représente les critères de filtre sur la recherche de dossier en cours.
 *
 */
export class RechercheDossier {
    protected _etatsDossier: Set<EtatDossier>;
    protected _attributaires: Set<Redacteur | null>;

    constructor(attributaires: Array<Redacteur | null> = [], etatsDossier: EtatDossier[] = []) {
        this._attributaires = new Set(attributaires)
        this._etatsDossier = new Set(etatsDossier)
        makeObservable(this, {
            _attributaires: observable,
            _etatsDossier: observable,
            setAttributaire: action,
            attributaires: computed,
            etatsDossier: computed,
            estActif: observable,
        } as any)
    }

    estActif(attributaire: Redacteur | null): boolean {
        return this._attributaires.has(attributaire)
    }

    get attributaires(): Iterator<Redacteur | null> {
        return this._attributaires.values()
    }

    get etatsDossier(): EtatDossier[] {
        return this._etatsDossier.values().toArray()
    }

    public setAttributaire(attributaire: Redacteur | null, actif: boolean = true) {
        if (actif) {
            this._attributaires.add(attributaire)
        } else {
            this._attributaires.delete(attributaire)
        }
    }

    public toURL(): URL {
        const url = new URL(window.location.toString());

        if (this._attributaires.size > 0) {
            url.searchParams.set('a', this._attributaires.values().toArray().map((r: Redacteur | null) => r?.id ?? '_').sort().join('|'))
        } else {
            url.searchParams.delete('a');
        }

        if (this._etatsDossier.size > 0) {
            url.searchParams.set('e', this._etatsDossier.values().toArray().map((e: EtatDossier) => e.slug).sort().join('|'))
        } else {
            url.searchParams.delete('e');
        }

        return url;
    }

    public static fromURL(): RechercheDossier {
        const query = new URLSearchParams(window.location.search);

        return new RechercheDossier(
            query.has('a') ?
                query
                    .get('a')
                    .split('|')
                    .map((a) => a == '_' ? null : Redacteur.resoudre(parseInt(a))) :
                [],
            query.has('e') ? query
                    .get('e')
                    .split('|')
                .map((e) => EtatDossier.resoudre(e)) : []
        );
    }
}