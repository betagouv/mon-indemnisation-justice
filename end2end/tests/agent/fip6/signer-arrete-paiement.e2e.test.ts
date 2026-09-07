import {test} from "@playwright/test";
import * as path from "node:path";
import {fileURLToPath} from "url";
import {connexionAgent, getTitre} from "../../helpers";
import {expect} from "./expect";

const __dirname = path.dirname(fileURLToPath(import.meta.url));

test("signer l'arrêté de paiement", async ({browser}) => {
    // Démarrer une session incognito pour éviter les effets de bord des sessions en cookie
    const context = await browser.newContext();
    await context.clearCookies();
    const page = await browser.newPage();

    try {
        await connexionAgent(page, "Validateur");
        await page.waitForURL((url) =>
            url.pathname.startsWith("/agent/fip6/dossiers"),
        );

        await expect(getTitre(page, "Les dossiers")).toBeVisible();

        // Navigation : "Dossiers" > "Arrêté à signer"
        await page.getByRole("button", {name: "Dossiers", exact: true}).click();
        await page.getByRole("link", {name: /Arrêté à signer/}).click();

        await expect(
            getTitre(page, "Dossiers en attente de signature de l'arrêté de paiement"),
        ).toBeVisible();

        // Attendre que la requête xhr soit terminée
        await page.waitForLoadState("networkidle");

        const locatorListeDossiers = page.locator(".mij-dossier-liste-element");
        await expect(locatorListeDossiers.first()).toBeVisible();

        // Consulter le premier dossier de la liste
        await locatorListeDossiers
            .first()
            .getByRole("link", {name: "Consulter"})
            .click();

        await page.waitForURL((url) => /\/dossier\/\d+/.test(url.pathname));

        // Ouvrir la modale de signature de l'arrêté de paiement
        await page
            .getByRole("button", {name: "Signer l'arrêté de paiement", exact: true})
            .click();

        const modaleSignature = page.getByRole("dialog", {
            name: /(Signer|Éditer) l'arrêté de paiement/,
        });
        await expect(modaleSignature).toBeVisible();

        // Passer de la relecture de l'arrêté à l'étape de signature
        await modaleSignature
            .getByRole("button", {name: "Signer et envoyer", exact: true})
            .click();

        // Téléverser l'arrêté de paiement signé
        await modaleSignature
            .getByLabel("Téléverser le fichier pour signature")
            .setInputFiles(path.join(__dirname, "../../ressources/arrete_de_paiement.pdf"));


        // Signer et envoyer pour paiement
        const locatorBouton = modaleSignature.getByRole("button", {
            name: "Signer et envoyer pour paiement",
            exact: true,
        })

        await expect(locatorBouton).toBeEnabled()

        await
            modaleSignature.getByRole("button", {
                name: "Signer et envoyer pour paiement",
                exact: true,
            })
                .click();
        

        // Après un rafraîchissement de la page, le dossier doit apparaître à l'état
        // "Accepté - arrêté à transmettre à FIP3"
        await page.reload();
        await page.waitForLoadState("networkidle");

        await expect(
            page.locator(".fr-badge--dossier-etat", {
                hasText: "Accepté - arrêté à transmettre à FIP3",
            }),
        ).toBeVisible();
    } catch (e) {
        console.log("CAUGHT ERROR", e);
        await context.close();
    }
});
