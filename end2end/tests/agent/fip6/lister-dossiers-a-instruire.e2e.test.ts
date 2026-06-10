import {expect, test} from "@playwright/test";
import {connexionAgent, getTitre} from "../../helpers";

test("lister dossier à instruire", async ({browser}) => {
    // Démarrer une session incognito pour éviter les effets de bord des sessions en cookie
    const context = await browser.newContext();
    await context.clearCookies();
    const page = await browser.newPage();

    try {
        await connexionAgent(page, "Rédacteur");
        await page.waitForURL((url) => url.pathname.startsWith("/agent/fip6/dossiers"));

        await expect(getTitre(page, "Les dossiers")).toBeVisible();

        await page.goto("/agent/fip6/dossiers/a-instruire");

        await expect(
            page.locator("H1", {hasText: "Dossiers à instruire"}),
        ).toBeVisible();

        // Attendre que la requête xhr soit terminée
        await page.waitForSelector("h4[text=2 dossiers]", {
            state: "visible",
        });

        await expect(page.locator(".mij-dossier-liste-element")).toHaveCount(1);
    } catch (e) {
        await context.close();
    }
});
