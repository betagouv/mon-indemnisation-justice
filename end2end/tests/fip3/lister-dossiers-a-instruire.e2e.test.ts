import {test, expect} from "@playwright/test";
import {connexionAgent, getTitre} from "./helpers";

test("lister dossier à instruire", async ({page, browser}) => {
    // Démarrer une session incognito pour éviter les effets de bord des sessions en cookie
    const context = await browser.newContext();
    await context.clearCookies();

    try {
        await connexionAgent(page, "Rédacteur");
        await page.waitForURL("/agent/redacteur/dossiers");

        await expect(getTitre(page, "Les dossiers")).toBeVisible();

        await page.goto('/agent/fip3/dossiers/a-instruire')

        await expect(getTitre(page, "Dossiers à instruire")).toBeVisible();

        // Attendre que la requête xhr soit terminée
        await page.waitForLoadState('networkidle');

        await expect(getTitre(page, "2 dossiers", 'h4')).toBeVisible();

        await expect(page.locator('.mij-dossier-liste-element')).toHaveCount(2);
    } catch (e) {
        await context.close();
    }
});
