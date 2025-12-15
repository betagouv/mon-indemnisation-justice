import {test, expect} from "@playwright/test";
import {connexionAgent} from "../../helpers";

test("recherche dossier", async ({browser}) => {
    // Démarrer une session incognito pour éviter les effets de bord des sessions en cookie
    const context = await browser.newContext();
    await context.clearCookies();
    const page = await browser.newPage();

    try {
        await connexionAgent(page, "Rédacteur");
        await page.waitForURL("/agent/redacteur/dossiers");

        await expect(page.locator("h1", {hasText: "Les dossiers"})).toBeVisible();

        const locatorCorpsTableResultats = page
            .getByTestId("tableau-dossiers-resultats")
            .locator("tbody");

        // Changement de critère de recherche : ajouter les dossiers à finaliser
        await page.getByText("Critères de recherche").click();
        await page.waitForLoadState("networkidle");

        await page
            .getByLabel("Statut du dossier")
            .selectOption(["À finaliser", "À attribuer"]);

        await expect(locatorCorpsTableResultats).toBeVisible();
        await expect(locatorCorpsTableResultats.locator("tr")).toHaveCount(2);

        const locatorsRequerants = await locatorCorpsTableResultats
            .locator("tr td:nth-child(2) span:first-child")
            .all();
        await expect(locatorsRequerants.at(0)).toHaveText("Raquel RANDT");
    } catch (e) {
        await context.close();
    }
});
