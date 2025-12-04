import {Page} from "playwright-core";
import {expect, Locator} from "@playwright/test";

export const connexionAgent = async (page: Page, identifiant: string): Promise<void> => {
    await page.goto("/connexion");

    const locatorBoutonProConnect = page.locator("a.fr-btn, button", {
        hasText: new RegExp("S’identifier.*avec.*ProConnect", "su"),
    });

    await expect(locatorBoutonProConnect).toBeVisible();
    await locatorBoutonProConnect.click({timeout: 1000});

    const locatorBoutonConnexionRedacteur = page
        .locator("div.card", {hasText: identifiant})
        .getByText("Connecter");

    await expect(locatorBoutonConnexionRedacteur).toBeEnabled();

    await locatorBoutonConnexionRedacteur.click();
}

export const connexionFranceConnect = async (page: Page, identifiant: string): Promise<void> => {
    await page.goto("/connexion");

    const locatorBoutonFranceConnect = page.locator("a.fr-btn, button", {
        hasText: new RegExp("S’identifier.*avec.*FranceConnect", "su"),
    });

    await expect(locatorBoutonFranceConnect).toBeVisible();
    await locatorBoutonFranceConnect.click({timeout: 1000});

    const locatorBoutonConnexionRequerant = page
        .locator("div.card", {hasText: identifiant})
        .getByText("Connecter");

    await expect(locatorBoutonConnexionRequerant).toBeEnabled();

    await locatorBoutonConnexionRequerant.click();
}


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