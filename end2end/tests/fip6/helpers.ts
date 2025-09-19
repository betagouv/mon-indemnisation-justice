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


export type niveauDeTitre = 'h1' | 'h2' | 'h3' | 'h4' | 'h5' | 'h6' | 'h7';

export const getTitre = (page: Page, libelle: string, tag: niveauDeTitre = "h1"): Locator => {
    return page.locator(tag, {hasText: libelle});
}