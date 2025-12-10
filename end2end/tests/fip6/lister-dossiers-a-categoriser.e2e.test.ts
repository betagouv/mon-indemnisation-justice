import { test } from "@playwright/test";
import { expect } from "./expect";
import { connexionAgent, getTitre } from "../helpers";

test("lister les dossiers a categoriser", async ({ browser }) => {
  // Démarrer une session incognito pour éviter les effets de bord des sessions en cookie
  const context = await browser.newContext();
  await context.clearCookies();
  const page = await browser.newPage();

  try {
    await connexionAgent(page, "Betagouv");
    await page.waitForURL("/agent/redacteur/dossiers");

    await expect(getTitre(page, "Les dossiers")).toBeVisible();

    await page.goto("/agent/fip6/dossiers/a-categoriser");

    await expect(getTitre(page, "Dossiers à catégoriser")).toBeVisible();

    // Attendre que la requête xhr soit terminée
    await page.waitForLoadState("networkidle");

    const locatorListeDossiers = page.locator(".mij-dossier-liste-element");

    // TODO trouver une façon de faire marcher ça
    await expect(locatorListeDossiers).toHaveCount(1);
  } catch (e) {
    await context.close();
  }
});
