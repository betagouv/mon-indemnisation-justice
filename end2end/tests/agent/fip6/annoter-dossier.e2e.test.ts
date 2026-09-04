import { test } from "@playwright/test";
import { connexionAgent, getTitre } from "../../helpers";
import { expect } from "./expect";

test("annoter un dossier en cours d'instruction", async ({ browser }) => {
  // Démarrer une session incognito pour éviter les effets de bord des sessions en cookie
  const context = await browser.newContext();
  await context.clearCookies();
  const page = await browser.newPage();

  try {
    await connexionAgent(page, "Rédacteur");
    await page.waitForURL((url) =>
      url.pathname.startsWith("/agent/fip6/dossiers"),
    );

    await expect(getTitre(page, "Les dossiers")).toBeVisible();

    // Navigation : "Dossiers" > "À instruire"
    await page.getByRole("button", { name: "Dossiers", exact: true }).click();
    await page.getByRole("link", { name: /À instruire/ }).click();

    await expect(getTitre(page, "Dossiers à instruire")).toBeVisible();

    // Attendre que la requête xhr soit terminée
    await page.waitForLoadState("networkidle");

    const locatorListeDossiers = page.locator(".mij-dossier-liste-element");
    await expect(locatorListeDossiers.first()).toBeVisible();

    // Consulter le premier dossier de la liste
    await locatorListeDossiers
      .first()
      .getByRole("link", { name: "Consulter" })
      .click();

    await page.waitForURL((url) => /\/dossier\/\d+/.test(url.pathname));

    // Un dossier "à instruire" n'autorise la prise de notes qu'une fois
    // l'instruction démarrée
    await page
      .getByRole("button", { name: "Démarrer l'instruction", exact: true })
      .click();

    await expect(
      page.locator(".fr-badge--dossier-etat", {
        hasText: "En cours d'instruction",
      }),
    ).toBeVisible();

    // Ouvrir l'onglet "Notes de suivi"
    await page.getByRole("tab", { name: "Notes de suivi" }).click();

    const note = "Ceci est une note du rédacteur";

    const editeur = page.locator(".ql-editor");
    await editeur.click();
    await editeur.pressSequentially(note);

    const [reponseAnnotation] = await Promise.all([
      page.waitForResponse(
        (reponse) =>
          reponse.url().includes("/annoter") &&
          reponse.request().method() === "POST",
      ),
      page
        .getByRole("button", { name: "Enregistrer les changements" })
        .click(),
    ]);
    expect(reponseAnnotation.ok()).toBeTruthy();

    // Après un rafraîchissement de la page, la note doit avoir été conservée
    await page.reload();
    await page.waitForLoadState("networkidle");

    await expect(page.getByRole("tab", { name: "Notes de suivi" })).toHaveAttribute(
      "aria-selected",
      "true",
    );

    await expect(page.locator(".ql-editor")).toHaveText(note);
  } catch (e) {
    await context.close();
  }
});
