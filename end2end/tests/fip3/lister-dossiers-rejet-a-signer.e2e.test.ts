import {test, expect} from "@playwright/test";
import {connexionAgent, getTitre} from "./helpers";

test("lister dossiers dont le courrier de rejet est à signer", async ({page, browser}) => {
    // Démarrer une session incognito pour éviter les effets de bord des sessions en cookie
    const context = await browser.newContext();
    await context.clearCookies();

    try {
        await connexionAgent(page, "Validateur");
        await page.waitForURL("/agent/redacteur/dossiers");

        await expect(getTitre(page, "Les dossiers")).toBeVisible();

        await page.goto('/agent/fip3/dossiers/rejet-a-signer')

        await expect(getTitre(page, "Dossiers en attente de signature du courrier de rejet")).toBeVisible();

        // Attendre que la requête xhr soit terminée
        await page.waitForLoadState('networkidle');

        await expect(getTitre(page, "1 dossier", 'h4')).toBeVisible();

        await expect(page.locator('.mij-dossier-liste-element')).toHaveCount(1);
    } catch (e) {
        await context.close();
    }
});
