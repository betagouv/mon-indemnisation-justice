import { test, expect } from "@playwright/test";

test("recherche dossier", async ({ page }) => {
  // TODO: factoriser dans des helpers https://medium.com/@divyakandpal93/writing-reusable-functions-and-custom-helpers-in-playwright-7176c3c0c490
  await page.goto("/connexion");

  const locatorBoutonProConnect = page.locator("a.fr-btn, button", {
    hasText: new RegExp("S’identifier.*avec.*ProConnect", "su"),
  });

  await expect(locatorBoutonProConnect).toBeVisible();
  await locatorBoutonProConnect.click();

  const locatorBoutonConnexionRedacteur = page
    .locator("div.card", { hasText: "Rédacteur" })
    .getByText("Connecter");

  await expect(locatorBoutonConnexionRedacteur).toBeEnabled();

  await locatorBoutonConnexionRedacteur.click();

  await page.waitForURL("/agent/redacteur/dossiers");

  await expect(page.locator("h1", { hasText: "Les dossiers" })).toBeVisible();

  const locatorCorpsTableResultats = page
    .getByTestId("tableau-dossiers-resultats")
    .locator("tbody");

  // Changement de critère de recherche : ajouter les dossiers à finaliser
  await page.getByText("Critères de recherche").click();
  await page.waitForLoadState("networkidle");

  await page
    .getByLabel("Statut du dossier")
    .selectOption(["À finaliser", "Attribué - à instruire"]);

  await expect(locatorCorpsTableResultats).toBeVisible();
  await expect(locatorCorpsTableResultats.locator("tr")).toHaveCount(2);

  const locatorsRequerants = await locatorCorpsTableResultats
    .locator("tr td:nth-child(2) span:first-child")
    .all();
  await expect(locatorsRequerants.at(0)).toHaveText("Raquel RANDT");
  await expect(locatorsRequerants.at(1)).toHaveText("Ray KERAN");
});
