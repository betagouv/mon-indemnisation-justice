import {test} from "@playwright/test";
import {connexionAgent, getTitre} from "../../helpers";
import {expect} from "./expect";

test("attribuer un dossier à un rédacteur", async ({browser}) => {
    // Démarrer une session incognito pour éviter les effets de bord des sessions en cookie
    const context = await browser.newContext();
    await context.clearCookies();
    const page = await browser.newPage();

    try {
        await connexionAgent(page, "Attributeur");
        await page.waitForURL((url) =>
            url.pathname.startsWith("/agent/fip6/dossiers"),
        );

        await expect(getTitre(page, "Les dossiers")).toBeVisible();

        // Navigation : "Dossiers" > "À attribuer"
        await page.getByRole("button", {name: "Dossiers", exact: true}).click();
        await page.getByRole("link", {name: /À attribuer/}).click();

        await expect(getTitre(page, "Dossiers à attribuer")).toBeVisible();

        // Attendre que la requête xhr soit terminée
        await page.waitForLoadState("networkidle");

        const locatorListeDossiers = page.locator(".mij-dossier-liste-element");
        await expect(locatorListeDossiers.first()).toBeVisible();

        // Consulter le premier dossier de la liste
        await locatorListeDossiers
            .first()
            .getByRole("link", {name: "Consulter"})
            .click();

        await page.waitForURL((url) => /\/dossier\/\d+/.test(url.pathname));

        // Ouvrir la modale d'attribution
        const boutonAttribuer = page.getByRole("button", {
            name: "Attribuer",
            exact: true,
        });
        await expect(boutonAttribuer).toBeVisible();
        await boutonAttribuer.click();

        const modaleAttribution = page.getByRole("dialog", {
            name: "Attribuer le dossier",
        });
        await expect(modaleAttribution).toBeVisible();

        await modaleAttribution
            .getByLabel("Rédacteur")
            .selectOption({label: "Red ACTEUR"});

        await modaleAttribution
            .getByRole("button", {name: "Attribuer", exact: true})
            .click();

        await expect(modaleAttribution).toBeHidden();

        // Le dossier est désormais attribué à "Red ACTEUR" et attend d'être instruit
        await expect(
            page.locator("p", {hasText: "est attribué à Red ACTEUR"}),
        ).toBeVisible();

        await expect(
            page.locator(".fr-badge--dossier-etat", {
                hasText: "Attribué - à instruire",
            }),
        ).toBeVisible();
    } catch (e) {
        console.error(e);
        await context.close();
    }
});
