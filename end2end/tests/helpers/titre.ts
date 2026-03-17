import {Page} from "playwright-core";
import {Locator} from "@playwright/test";

export type niveauDeTitre = 'h1' | 'h2' | 'h3' | 'h4' | 'h5' | 'h6' | 'h7';

export const getTitre = (page: Page, libelle: string, tag: niveauDeTitre = "h1"): Locator => {
    return page.locator(tag, {hasText: libelle});
}

/**
 * Récupérer le `<dd>` qui suit un `<dt>` dont le texte est donné en paramètre
 */
export const getItemDescription = (page: Page, libelle: string): Locator => {

    return page.locator(`dt:has-text("${libelle}") + dd`);
}