import {test, expect} from "@playwright/test";
import {connexionAgent, getTitre} from "../../helpers";

test("FDO - Gendarme - déclarer bris de porte", async ({browser}) => {
    test.skip(!!process.env.CI, "Test désactive - impossible de le faire marcher en CI");

    // Démarrer une session incognito pour éviter les effets de bord des sessions en cookie
    const context = await browser.newContext();
    await context.clearCookies();
    const page = await browser.newPage();

    await connexionAgent(page, "Gendarme");
    await page.waitForURL("/agent/fdo");

    await expect(page.getByText("Déclarer un bris de porte")).toBeVisible();

    await page.getByText("Déclarer un bris de porte").click();

    await page.waitForURL(/\/agent\/fdo\/bris-de-porte\/[0-9a-f\-]+\/1-bris-de-porte/);
    await expect(getTitre(page, "Nouvelle déclaration de bris de porte", 'h1')).toBeVisible();
    await expect(getTitre(page, "Eléments relatifs au bris de porte", 'h2')).toBeVisible();

    // Étape 1 : éléments relatifs au bris de porte
    await page
        .locator("fieldset", {
            has: page.getByText(
                /S’agissait-il d’une .*erreur opérationnelle ?/,
            ),
        })
        .getByLabel("Oui")
        .check();
    await page
        .getByLabel("Décrivez l’opération")
        .fill("Nous sommes intervenus à l'adresse qui nous avait été indiqué.\nAprès avoir frappé à la porte, personne n'ayant répondu nous avons opté d'ouvrir.");
    await page.getByLabel("Date de l'opération").fill("2025-12-15");
    await page.getByLabel("Adresse du logement ayant subi le bris de porte").fill("125 boulevard des Fleurs");
    await page.getByLabel("Complément d'adresse").fill("Porte B");
    await page.getByLabel("Code postal").fill("75021");
    await page.getByLabel("Ville").fill("PARIS");


    // Attendre 250ms que les données soient "enregistrées"
    await page.waitForTimeout(500);
    await page.getByText("Continuer", {exact: true}).click();

    await page.waitForURL(/\/agent\/fdo\/bris-de-porte\/[0-9a-f\-]+\/2-service-enqueteur/);

    await expect(getTitre(page, "Nouvelle déclaration de bris de porte", 'h1')).toBeVisible();
    await expect(getTitre(page, "Éléments relatifs au service enquêteur", 'h2')).toBeVisible();

    // Étape 2 : éléments relatifs au service enquêteur
    await page.getByLabel("Service enquêteur").fill("SDPJ");
    await page.getByLabel("Téléphone du service ou de l'agent").fill("0123456789");
    await page.getByLabel("Numéro de procédure").fill("PRO1276");
    await page.getByLabel(/^Juridiction \/ parquet/).fill("PARIS");
    await page.getByLabel(/^Nom du magistrat/).fill("M MARTEAU");

    // Attendre 250ms que les données soient "enregistrées"
    await page.waitForTimeout(500);
    await page.getByText("Continuer", {exact: true}).click();

    await page.waitForURL(/\/agent\/fdo\/bris-de-porte\/[0-9a-f\-]+\/3-usager/);

    await expect(getTitre(page, "Nouvelle déclaration de bris de porte", 'h1')).toBeVisible();
    await expect(getTitre(page, "Informations concernant l'usager", 'h2')).toBeVisible();

    // Étape 3 : informations concernant l'usager
    await page
        .locator("fieldset", {
            has: page.getByText(
                "J’ai les coordonnées de l’usager",
            ),
        })
        .getByLabel("Oui")
        .check();
    await page.getByLabel("Civilité").selectOption("Madame");
    await page.getByLabel(/^Nom/).fill("Rente");
    await page.getByLabel("Prénom").fill("Erika");
    await page.getByLabel("Téléphone").fill("0456128301");
    await page.getByLabel("Courriel").fill("erika.rente@courriel.fr");
    await page.getByLabel("Précisions concernant l'usager").fill("Propriétaire occupant");

    // Attendre 250ms que les données soient "enregistrées"
    await page.waitForTimeout(500);
    await page.getByText("Envoyer", {exact: true}).click();

    await page.waitForURL(/\/agent\/fdo\/bris-de-porte\/mes-declarations/);


    await expect(getTitre(page, "Mes déclarations", 'h1')).toBeVisible();

    // Attendre que la requête xhr soit terminée
    await page.waitForLoadState("networkidle");

    const ligneDeclaration = page.locator('div.fr-table table').locator('tbody').locator('tr:last-child');

    await expect(ligneDeclaration).toBeVisible();

    await expect(ligneDeclaration.locator('td').nth(2)).toHaveText("125 boulevard des Fleurs 75021 PARIS");
    await expect(ligneDeclaration.locator('td').nth(1)).toHaveText(/^15 décembre/);
});

test("FDO - Policier - déclarer bris de porte", async ({browser}) => {
    // Démarrer une session incognito pour éviter les effets de bord des sessions en cookie
    const context = await browser.newContext();
    await context.clearCookies();
    const page = await browser.newPage();

    await connexionAgent(page, "Policier");
    await page.waitForURL("/agent/fdo");

    await expect(page.getByText("Déclarer un bris de porte")).toBeVisible();

    await page.getByText("Déclarer un bris de porte").click();

    await page.waitForURL(/\/agent\/fdo\/bris-de-porte\/[0-9a-f\-]+\/1-bris-de-porte/);
    await expect(getTitre(page, "Nouvelle déclaration de bris de porte", 'h1')).toBeVisible();
    await expect(getTitre(page, "Eléments relatifs au bris de porte", 'h2')).toBeVisible();

    // Étape 1 : éléments relatifs au bris de porte
    await page
        .locator("fieldset", {
            has: page.getByText(
                /S’agissait-il d’une .*erreur opérationnelle ?/,
            ),
        })
        .getByLabel("Oui")
        .check();
    await page
        .getByLabel("Décrivez l’opération")
        .fill("Nous sommes intervenus à l'adresse qui nous avait été indiqué.\nAprès avoir frappé à la porte, personne n'ayant répondu nous avons opté d'ouvrir.");
    await page.getByLabel("Date de l'opération").fill("2025-12-14");
    await page.getByLabel("Adresse du logement ayant subi le bris de porte").fill("4 allée des Fruits");
    await page.getByLabel("Complément d'adresse").fill("2è étage");
    await page.getByLabel("Code postal").fill("69010");
    await page.getByLabel("Ville").fill("LYON");

    // Attendre 250ms que les données soient "enregistrées"
    await page.waitForTimeout(500);
    await page.getByText("Continuer", {exact: true}).click();

    await page.waitForURL(/\/agent\/fdo\/bris-de-porte\/[0-9a-f\-]+\/2-service-enqueteur/);

    await expect(getTitre(page, "Nouvelle déclaration de bris de porte", 'h1')).toBeVisible();
    await expect(getTitre(page, "Éléments relatifs au service enquêteur", 'h2')).toBeVisible();

    // Étape 2 : éléments relatifs au service enquêteur
    await page.getByLabel("Service enquêteur").fill("DPNU");
    await page.getByLabel("Téléphone du service ou de l'agent").fill("0456789012");
    await page.getByLabel("Numéro de procédure").fill("PRO3185");
    await page.getByLabel(/^Juridiction \/ parquet/).fill("LYON");
    await page.getByLabel(/^Nom du magistrat/).fill("M BALANCE");

    // Attendre 250ms que les données soient "enregistrées"
    await page.waitForTimeout(500);
    await page.getByText("Continuer", {exact: true}).click();

    await page.waitForURL(/\/agent\/fdo\/bris-de-porte\/[0-9a-f\-]+\/3-usager/);

    await expect(getTitre(page, "Nouvelle déclaration de bris de porte", 'h1')).toBeVisible();
    await expect(getTitre(page, "Informations concernant l'usager", 'h2')).toBeVisible();

    // Étape 3 : informations concernant l'usager
    await page
        .locator("fieldset", {
            has: page.getByText(
                "J’ai les coordonnées de l’usager",
            ),
        })
        .getByLabel("Non")
        .check();
    await page.getByLabel("Précisions").fill("Logement vacant lors de notre passage");

    // Attendre 250ms que les données soient "enregistrées"
    await page.waitForTimeout(500);
    await page.getByText("Envoyer", {exact: true}).click();

    await page.waitForURL(/\/agent\/fdo\/bris-de-porte\/mes-declarations/);

    await expect(getTitre(page, "Mes déclarations", 'h1')).toBeVisible();

    // Attendre que la requête xhr soit terminée
    await page.waitForLoadState("networkidle");

    const ligneDeclaration = page.locator('div.fr-table table tbody tr:last-child')

    await expect(ligneDeclaration).toBeVisible();

    await expect(ligneDeclaration.locator('td').nth(2)).toHaveText("4 allée des Fruits 69010 LYON");
    await expect(ligneDeclaration.locator('td').nth(1)).toHaveText(/^14 décembre/);
});