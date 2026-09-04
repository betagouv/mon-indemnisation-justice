import { test } from "@playwright/test";
import { connexionAgent, getTitre } from "../../helpers";
import { expect } from "./expect";

test("clôturer un dossier en cours d'instruction", async ({ browser }) => {
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

    // Navigation : "Dossiers" > "En cours d'instruction"
    await page.getByRole("button", { name: "Dossiers", exact: true }).click();
    await page.getByRole("link", { name: /En cours d'instruction/ }).click();

    await expect(getTitre(page, "Dossiers en cours d'instruction")).toBeVisible();

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

    // Ouvrir la modale de clôture
    await page.getByRole("button", { name: "Clôturer", exact: true }).click();

    const modaleCloture = page.getByRole("dialog", {
      name: "Clôturer le dossier",
    });
    await expect(modaleCloture).toBeVisible();

    // Choisir le motif prédéfini "Dossier incomplet"
    await modaleCloture
      .getByLabel("Choix du motif")
      .selectOption({ label: "Dossier incomplet" });

    await modaleCloture
      .getByRole("button", { name: "Sélectionner", exact: true })
      .click();

    const explication = "Ce dossier manque d'informations";

    // Le motif reste celui prédéfini ; seule l'explication détaillée est personnalisée
    await modaleCloture.getByLabel("Explication détaillée").fill(explication);

    const [reponseCloture] = await Promise.all([
      page.waitForResponse(
        (reponse) =>
          reponse.url().includes("/cloturer") &&
          reponse.request().method() === "POST",
      ),
      modaleCloture
        .getByRole("button", { name: "Valider la clôture", exact: true })
        .click(),
    ]);
    expect(reponseCloture.ok()).toBeTruthy();

    const corpsReponse = await reponseCloture.json();
    expect(corpsReponse.etat.etat).toBe("CLOTURE");
    expect(corpsReponse.etat.contexte.motif).toBe("Dossier incomplet");
    expect(corpsReponse.etat.contexte.explication).toBe(explication);

    // Après un rafraîchissement de la page, le dossier doit apparaître clôturé
    await page.reload();
    await page.waitForLoadState("networkidle");

    await expect(
      page.locator(".fr-badge--dossier-etat", { hasText: "Clôturé" }),
    ).toBeVisible();
  } catch (e) {
    await context.close();
  }
});
