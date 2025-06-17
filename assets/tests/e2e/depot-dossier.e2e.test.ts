import { test, expect } from "@playwright/test";
import { fileURLToPath } from "url";
import * as path from "node:path";

const __dirname = path.dirname(fileURLToPath(import.meta.url));

test("dépôt de dossier", async ({ page }) => {
  await page.goto("/connexion");

  await expect(
    page.locator("a.fr-btn, button", {
      hasText: new RegExp("S’identifier.*avec.*FranceConnect", "su"),
    }),
  ).toBeVisible();

  await expect(
    page.getByText("S'identifier avec son adresse email"),
  ).toBeVisible();

  await expect(
    page.locator("a.fr-btn, button", {
      hasText: new RegExp("S’identifier.*avec.*FranceConnect", "su"),
    }),
  ).toBeVisible();

  await page.getByText("S'identifier avec son adresse email").click();

  await expect(page.getByText("Je me connecte à mon espace")).toBeVisible();

  await page
    .getByLabel("Adresse courriel")
    .filter({ visible: true })
    .fill("raquel.randt@courriel.fr");
  await page.getByLabel("Mot de passe").first().fill("P4ssword");

  await expect(page.getByText("Je me connecte à mon espace")).toBeEnabled();

  await page.getByText("Je me connecte à mon espace").click();

  await page.waitForURL(
    "/requerant/bris-de-porte/declarer-un-bris-de-porte/*",
    {
      timeout: 30000,
    },
  );

  await expect(
    page.getByText("Déclarer un bris de porte", { exact: true }),
  ).toBeVisible();

  await expect(
    page.locator("h2", { hasText: "Données personnelles" }),
  ).toBeVisible();

  await page
    .getByLabel("Les 10 premiers chiffres de votre numéro de sécurité sociale")
    .fill("2790656123");
  await page.getByLabel("Ville de naissance").fill("Turenne");
  await page.getByLabel("Pays de naissance").selectOption("France");
  await page.getByLabel("Date de naissance").fill("1979-05-17");

  await expect(
    page.getByText("Valider et passer à l'étape suivante"),
  ).toBeEnabled();
  await page.getByText("Valider et passer à l'étape suivante").click();

  await expect(
    page.getByText("Déclarer un bris de porte", { exact: true }),
  ).toBeVisible();

  await expect(
    page.locator("h2", { hasText: "Informations relatives au bris de porte" }),
  ).toBeVisible();

  const today = new Date();
  const yesterday: Date = new Date();
  yesterday.setDate(today.getDate() - 1);

  await page
    .getByLabel("Date de l'opération de police judiciaire")
    .fill(yesterday.toISOString().split("T")[0]);

  await page
    .getByLabel("Adresse du logement concerné par le bris de porte")
    .fill("17 rue des oliviers");
  await page.getByLabel("Complément d'adresse").fill("Escalier B, 3è étage");
  await page.getByLabel("Code postal").fill("13008");
  await page.getByLabel("Ville").fill("Marseille");
  await page
    .locator("fieldset", {
      has: page.getByText("S'agit-il d'une porte blindée ?"),
    })
    .getByLabel("Oui")
    .check();
  await page
    .getByLabel("Vous effectuez votre demande en qualité de")
    .selectOption("Propriétaire");

  await expect(
    page.getByText("Valider et passer à l'étape suivante"),
  ).toBeEnabled();
  await page.getByText("Valider et passer à l'étape suivante").click();

  await expect(
    page.getByText("Déclarer un bris de porte", { exact: true }),
  ).toBeVisible();

  await expect(
    page.locator("h2", {
      hasText: "Documents à joindre obligatoirement à votre demande",
    }),
  ).toBeVisible();

  // Select one file
  await page
    .getByLabel("Attestation complétée par les forces de l'ordre")
    .setInputFiles(
      path.join(
        __dirname,
        "../ressources/attestation_completee_par_les_forces_de_l_ordre.pdf",
      ),
    );

  await expect(
    page.getByText("Valider et passer à l'étape suivante"),
  ).toBeEnabled();
  await page.getByText("Valider et passer à l'étape suivante").click();

  await expect(
    page.getByText("Déclarer un bris de porte", { exact: true }),
  ).toBeVisible();

  await expect(
    page.locator("h2", {
      hasText: "Vérification et soumission de votre demande",
    }),
  ).toBeVisible();

  await expect(page.getByText("Je déclare mon bris de porte")).toBeEnabled();
  await page.getByText("Je déclare mon bris de porte").click();

  await expect(page).toHaveURL("/requerant/mes-demandes");

  // TODO vérifier que la modale de confirmation est bien affichée
  // TODO vérifier qu'un email est bien reçu (cf. PHP) :
  /*
  // Je dois avoir reçu un courrier de confirmation
  // S'assurer que le requérant a bien reçu un email
  $response = $this->mailerClient->get('/api/v1/search', [
      'query' => [
          'query' => 'to:raquel.randt@courriel.fr subject:"Votre déclaration de bris de porte a bien été prise en compte"',
      ],
  ]);
  $result = json_decode($response->getBody()->getContents(), true);
   */
});
