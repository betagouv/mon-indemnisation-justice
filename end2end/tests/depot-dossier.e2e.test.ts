import {expect, test} from "@playwright/test";
import {fileURLToPath} from "url";
import * as path from "node:path";
import {formatYmd, hier, selectionnerBoutonRadio} from "./helpers";
import {remplirChamp, selectionnerMenu} from "./helpers/formulaire";

const __dirname = path.dirname(fileURLToPath(import.meta.url));

test("dépôt de dossier", async ({browser}) => {
    // Démarrer une session incognito pour éviter les effets de bord des sessions en cookie
    const context = await browser.newContext();
    await context.clearCookies();

    const page = await browser.newPage();

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
        .filter({visible: true})
        .fill("raquel.randt@courriel.fr");
    await page.getByLabel("Mot de passe").first().fill("P4ssword");

    await expect(page.getByText("Je me connecte à mon espace")).toBeEnabled();

    await page.getByText("Je me connecte à mon espace").click();

    await page.waitForURL(
        /\/requerant\/dossier\/bris-de-porte\/\d+\/1-bris-porte$/,
        {
            timeout: 30000,
        },
    );

    await expect(
        page.getByText("Déclarer un bris de porte", {exact: true}),
    ).toBeVisible();

    await expect(
        page.locator("h2", {hasText: "Informations relatives au bris de porte"}),
    ).toBeVisible();


    await selectionnerBoutonRadio(page, "Je suis", "Une personne physique");
    await selectionnerMenu(page, "Vous effectuez votre demande en qualité de ", "Propriétaire occupant");

    await remplirChamp(page, "Date du bris de porte", formatYmd(hier()));
    await remplirChamp(page, "Décrivez-nous l’intervention", "Les forces de l'ordre sont intervenues à 6h du matin et ont cassé ma porte.\nIls sont repartis aussitôt.");

    await remplirChamp(page, "Adresse du logement concerné par le bris de porte", "17 rue des oliviers");
    await remplirChamp(page, "Complément d'adresse", "Escalier B, 3è étage");
    await remplirChamp(page, "Code postal", "13008");
    await remplirChamp(page, "Ville", "Marseille");

    await selectionnerBoutonRadio(page, "S'agit-il d'une porte blindée ?", "Oui");

    await expect(
        page.getByText("Valider et passer à l'étape suivante"),
    ).toBeEnabled();
    await page.getByText("Valider et passer à l'étape suivante").click();

    await expect(
        page.getByText("Déclarer un bris de porte", {exact: true}),
    ).toBeVisible();

    await expect(
        page.locator("h2", {hasText: "Données personnelles"}),
    ).toBeVisible();

    await remplirChamp(page, "Nom de naissance", "Quintana");
    await selectionnerMenu(page, "Pays de naissance", "France");
    //await page.getByLabel("Ville de naissance").fill("Turenne");
    await remplirChamp(page, "Date de naissance", "1979-05-17");


    // Laissons le temps au script de patcher
    await page.waitForTimeout(500);

    await expect(
        page.getByText("Valider et passer à l'étape suivante"),
    ).toBeEnabled();
    await page.getByText("Valider et passer à l'étape suivante").click();

    await expect(
        page.getByText("Déclarer un bris de porte", {exact: true}),
    ).toBeVisible();

    await expect(
        page.locator("h2", {
            hasText: "Documents à joindre à votre demande",
        }),
    ).toBeVisible();

    // Select one file
    await page
        .getByText(/Attestation complétée par les forces de l'ordre.*/)
        .locator("..")
        .getByLabel("Document à téléverser")

        .setInputFiles(
            path.join(
                __dirname,
                "./ressources/attestation_completee_par_les_forces_de_l_ordre.pdf",
            ),
        );

    await expect(
        page.getByText("Soumettre ma demande"),
    ).toBeEnabled();
    await page.getByText("Soumettre ma demande").click();

    /*
    L'étape 4, de vérification, est supprimée
    await expect(
        page.getByText("Déclarer un bris de porte", {exact: true}),
    ).toBeVisible();

    await expect(
        page.locator("h2", {
            hasText: "Vérification et soumission de votre demande",
        }),
    ).toBeVisible();

    await expect(page.getByText("Je déclare mon bris de porte")).toBeEnabled();
    await page.getByText("Je déclare mon bris de porte").click();

     */

    await expect(page).toHaveURL("/requerant/mes-demandes");

    // TODO vérifier que la modale de confirmation est bien affichée
    // TODO vérifier qu'un email est bien reçu (cf. PHP) :
    // Section désactivée puisque la modale apparait de façon aléatoire et fait crasher les tests fonctionnels
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

    /*
    // Fermer la modale si jamais elle s'affiche (pas systématique)
    if (
      await page
        .locator("#fr-modal-flash-dossier button.fr-btn--close")
        .isVisible()
    ) {
      await page.locator("#fr-modal-flash-dossier button.fr-btn--close").click();
    }

    await expect(page.getByText("Modifier")).toBeVisible();
    await page.getByText("Modifier").click();

    await expect(page).toHaveURL(
      /\/requerant\/bris-de-porte\/declarer-un-bris-de-porte\/\d+/,
    );
    await expect(
      page.getByText("Vérification et soumission de votre demande"),
    ).toBeVisible();

    // Vérifier que les changements ont bien été persistés

    await expect(
      page.locator(`dt:has-text("Nom et prénom :") + dd`),
    ).toBeVisible();

    await expect(getItemDescription(page, "Nom et prénom :")).toHaveText(
      "MME Raquel RANDT",
    );
    await expect(getItemDescription(page, "Née :")).toHaveText("le 17 mai 1979");
    await expect(
      getItemDescription(page, "Description de l'intervention :"),
    ).toHaveText(
      /Les forces de l'ordre sont intervenues à 6h du matin et ont cassé ma porte\.*Ils sont repartis aussitôt./,
    );

     */
});
