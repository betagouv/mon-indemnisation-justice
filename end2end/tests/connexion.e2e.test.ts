import {test, expect} from '@playwright/test';
import {connexionFranceConnect} from "./helpers";

test.describe('navigation', () => {
    test.beforeEach(async ({page, browser}) => {

        // Démarrer une session incognito pour éviter les effets de bord des sessions en cookie
        const context = await browser.newContext();
        await context.clearCookies();
        await page.goto("/connexion");
    });

    test('connexion courriel ok', async ({page}) => {
        await page.locator("a.fr-btn, button", {
            hasText: new RegExp("S'identifier avec son adresse email", "su"),
        }).click();

        const boutonConnexion = page.locator("a.fr-btn, button", {hasText: "Je me connecte à mon espace"});
        await expect(boutonConnexion).toBeVisible();

        await page.getByLabel("Adresse courriel").filter({visible: true}).fill("raquel.randt@courriel.fr");
        await page.getByLabel("Mot de passe", {exact: true}).filter({visible: true}).fill("P4ssword");
        await boutonConnexion.click()

        await expect(page).toHaveURL((url: URL) => /^\/requerant\/(mes-demandes|bris-de-porte\/declarer-un-bris-de-porte\/\d+)/.test(url.pathname));
    });

    test('connexion courriel ko mot de passe invalide', async ({page}) => {
        await page.locator("a.fr-btn, button", {
            hasText: new RegExp("S'identifier avec son adresse email", "su"),
        }).click();

        const boutonConnexion = page.locator("a.fr-btn, button", {hasText: "Je me connecte à mon espace"});
        await expect(boutonConnexion).toBeVisible();

        await page.getByLabel("Adresse courriel").filter({visible: true}).fill("ray.keran@courriel.fr");
        await page.getByLabel("Mot de passe", {exact: true}).filter({visible: true}).fill("motDePasseInv4lide");
        await boutonConnexion.click()

        await expect(page).toHaveURL("/connexion");
        await expect(page.locator('div.fr-message--error', {hasText: "Identifiants invalides"})).toBeVisible()
    });

    test('connexion courriel ko courriel inconnu', async ({page}) => {
        await page.locator("a.fr-btn, button", {
            hasText: new RegExp("S'identifier avec son adresse email", "su"),
        }).click();

        const boutonConnexion = page.locator("a.fr-btn, button", {hasText: "Je me connecte à mon espace"});
        await expect(boutonConnexion).toBeVisible();

        await page.getByLabel("Adresse courriel").filter({visible: true}).fill("ray.queran@courriel.fr");
        await page.getByLabel("Mot de passe", {exact: true}).filter({visible: true}).fill("motDePasseInv4lide");
        await boutonConnexion.click()

        await expect(page).toHaveURL("/connexion");
        await expect(page.locator('div.fr-message--error', {hasText: "Identifiants invalides"})).toBeVisible()
    });

    test('connexion FranceConnect ok', async ({page}) => {
        await connexionFranceConnect(page, "Ray KERAN")

        await expect(page).toHaveURL(new RegExp("/requerant/mes-demandes"));
    });

    test('connexion FranceConnect ko compte inconnu', async ({page}) => {
        await connexionFranceConnect(page, "Jean MICHON")

        await expect(page).toHaveURL("/connexion");
    });
});