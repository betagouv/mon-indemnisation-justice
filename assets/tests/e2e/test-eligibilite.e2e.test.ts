import { test, expect } from "@playwright/test";

test("test éligibilité", async ({ page }) => {
  await page.goto("/");

  await page.getByText("Déposer votre demande d’indemnisation").first().click();

  await expect(page).toHaveURL("/bris-de-porte/tester-mon-eligibilite");

  // Single selection matching the value or label
  await page
    .getByLabel("Dans quel département se situe le logement ?")
    .selectOption("13 - Bouches-du-Rhône");

  await expect(
    page.getByLabel(
      "Vous souhaitez nous apporter des précisions sur l'intervention ? ",
    ),
  ).toBeVisible();

  await page
    .getByLabel(
      "Vous souhaitez nous apporter des précisions sur l'intervention ? ",
    )
    .fill("Porte fracassée à l'aube");

  await page.getByText("Question suivante").click();

  await expect(
    page.getByText(
      "Étiez-vous la personne recherchée par les forces de l’ordre lors de leur intervention ?",
    ),
  ).toBeVisible();

  await page
    .locator("fieldset", {
      has: page.getByText(
        "Étiez-vous la personne recherchée par les forces de l’ordre lors de leur intervention ?",
      ),
    })
    .getByLabel("Non")
    .check();

  await expect(
    page.getByText(
      "Est-ce que la personne recherchée par les forces de l'ordre réside ou est hébergée à l'adresse du logement ayant subi le bris de porte ?",
    ),
  ).toBeVisible();

  await page
    .locator("fieldset", {
      has: page.getByText(
        "Est-ce que la personne recherchée par les forces de l'ordre réside ou est hébergée à l'adresse du logement ayant subi le bris de porte ?",
      ),
    })
    .getByLabel("Non")
    .check();

  await expect(
    page.getByText(
      "Quel est votre statut par rapport au logement ayant subi le bris de porte ?",
    ),
  ).toBeVisible();

  await page
    .locator("fieldset", {
      has: page.getByText(
        "Quel est votre statut par rapport au logement ayant subi le bris de porte ?",
      ),
    })
    .getByLabel("Propriétaire")
    .check();

  await expect(
    page.getByText(
      "Avez-vous pris contact avec votre assurance et obtenu une attestation de non prise en charge du sinistre ?",
    ),
  ).toBeVisible();

  await page
    .locator("fieldset", {
      has: page.getByText(
        "Avez-vous pris contact avec votre assurance et obtenu une attestation de non prise en charge du sinistre ?",
      ),
    })
    .getByLabel("Oui")
    .check();

  await page.getByText("Commencer la demande d'indemnisation").click();

  await expect(page).toHaveURL("/bris-de-porte/creation-de-compte");

  await expect(
    page.getByText("S'inscrire avec une adresse email"),
  ).toBeVisible();

  await page.getByText("S'inscrire avec une adresse email").click();

  // Remplissage du formulaire
  await page.getByLabel("Civilité").selectOption("Monsieur");
  await page.getByLabel("Prénom").fill("Rick");
  await page.getByLabel("Nom de naissance").fill("Errant");
  await page.getByLabel("Nom d'usage").fill("Errant");
  await page.getByLabel("Adresse courriel").fill("rick.errant@courriel.fr");
  await page.waitForResponse("/bris-de-porte/tester-adresse-courriel");
  await page.getByLabel("Numéro de téléphone").fill("0612345678");
  await page.getByLabel("Mot de passe").first().fill("P4ssword");
  await page.getByLabel("Confirmation du mot de passe").fill("P4ssword");
  await page
    .getByLabel(
      "Je certifie avoir lu et accepté les Conditions générales d'utilisation",
    )
    .check();

  await expect(
    page.getByText("Valider mon inscription et poursuivre ma demande"),
  ).toBeEnabled();

  await page
    .getByText("Valider mon inscription et poursuivre ma demande")
    .click();

  await expect(page.getByText("Inscription en cours")).toBeEnabled({
    enabled: false,
  });

  await page.waitForURL("/bris-de-porte/finaliser-la-creation", {
    timeout: 30000,
  });

  await expect(
    page.locator("h2", { hasText: "Finaliser la création de votre compte" }),
  ).toBeVisible();
});
