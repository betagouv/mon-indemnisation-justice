import { expect, test } from "@playwright/test";
import { connexionAgent, formatYmd } from "../../helpers";
import { dateIlYaNJours } from "../../helpers/date";

test("FDO - Policier - s'affecter à un établissement", async ({ browser }) => {
  // Démarrer une session incognito pour éviter les effets de bord des sessions en cookie
  const context = await browser.newContext();
  await context.clearCookies();
  const page = await browser.newPage();

  await connexionAgent(page, "Commissaire");
  await page.waitForURL("/agent/fdo");

  // Affectation à un commissariat
  await expect(
    page.locator("dialog", {
      has: page.getByText("Affectation à un commissariat"),
    }),
  ).toBeVisible();

  await page.getByLabel(/^Établissement/).fill("paris");
  const suggestion = page.getByText(
    "Commissariat de police de Paris 20ème arrondissement",
  );
  await expect(suggestion).toBeVisible();
  await suggestion.click();

  await expect(page.getByLabel(/^Date d'affectation/)).toBeEnabled();
  await page
    .getByLabel("Date d'affectation")
    .fill(formatYmd(dateIlYaNJours(22)));

  await page.getByText("Enregistrer").click();

  await expect(
    page.locator("dialog", {
      has: page.getByText("Affectation à un commissariat"),
    }),
  ).toBeHidden();
});
