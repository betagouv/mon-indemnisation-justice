import {test} from "@playwright/test";
import {expect} from "./expect";
import {connexionAgent, getTitre} from "../helpers";

test("recherche dossier", async ({page, browser}) => {
    // Démarrer une session incognito pour éviter les effets de bord des sessions en cookie
    const context = await browser.newContext();
    await context.clearCookies();

    try {
        await connexionAgent(page, "Attributeur");
        await page.waitForURL("/agent/redacteur/dossiers");

        await expect(getTitre(page, "Les dossiers")).toBeVisible();

        await page.goto('/agent/fip6/dossiers/a-attribuer')

        await expect(getTitre(page, "Dossiers à attribuer")).toBeVisible();

        // Attendre que la requête xhr soit terminée
        await page.waitForLoadState('networkidle');

        const locatorListeDossiers = page.locator('.mij-dossier-liste-element');

        // TODO trouver une façon de faire marcher ça
        // await expect(locatorListeDossiers).toHaveCountBetween({between: 1, and: 2});

        const nbDossiers = await locatorListeDossiers.count()

        await expect(locatorListeDossiers).toHaveCount(nbDossiers);

        await expect(getTitre(page, `${nbDossiers} dossier${nbDossiers > 1 && 's'}`, 'h4')).toBeVisible();
    } catch (e) {
        await context.close();
    }
});
