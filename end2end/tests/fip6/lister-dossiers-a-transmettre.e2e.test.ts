import { test, expect } from "@playwright/test";
import { connexionAgent, getTitre } from "../helpers";

test("lister dossiers à transmettre au bureau du budget", async ({
  browser,
}) => {
  // Démarrer une session incognito pour éviter les effets de bord des sessions en cookie
  const context = await browser.newContext();
  await context.clearCookies();
  const page = await browser.newPage();

  try {
    await connexionAgent(page, "Liaison budget");
    await page.waitForURL("/agent/mon-compte");

    await expect(getTitre(page, "Mon compte")).toBeVisible();

    await page.goto("/agent/fip6/dossiers/a-transmettre");

    await expect(
      getTitre(page, "Dossiers à transmettre au bureau du budget"),
    ).toBeVisible();

    // Attendre que la requête xhr soit terminée
    await page.waitForLoadState("networkidle");

    await expect(getTitre(page, "1 dossier", "h4")).toBeVisible();

    await expect(page.locator(".mij-dossier-liste-element")).toHaveCount(1);
  } catch (e) {
    await context.close();
  }
});
