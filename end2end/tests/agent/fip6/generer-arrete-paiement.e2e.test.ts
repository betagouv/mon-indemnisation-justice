import {test} from "@playwright/test";
import {connexionAgent, getTitre} from "../../helpers";
import {expect} from "./expect";

test("générer l'arrêté de paiement après vérification de la déclaration d'acceptation", async ({
                                                                                                   browser,
                                                                                               }) => {
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

        // Navigation : "Dossiers" > "Arrêté à éditer"
        await page.getByRole("button", {name: "Dossiers", exact: true}).click();
        await page.getByRole("link", {name: /Arrêté à éditer/}).click();

        await expect(
            getTitre(page, "Dossiers en attente d'arrêté de paiement"),
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

        // Ouvrir la modale de vérification de la déclaration d'acceptation
        await page.screenshot({path: "/app/test-results/debug-dossier.png", fullPage: true});
        await page
            .getByRole("button", {
                name: "Générer l'arrêté de paiement",
                exact: true,
            })
            .click();

        const modaleArrete = page.getByRole("dialog", {
            name: "Vérifier la déclaration d'acceptation",
        });
        await expect(modaleArrete).toBeVisible();

        // La déclaration d'acceptation retournée par le requérant est affichée pour vérification
        await expect(
            modaleArrete.getByText(
                "Inspectez le document de déclaration d'acceptation ci-dessous",
            ),
        ).toBeVisible();

        // Valider la déclaration et lancer la génération de l'arrêté de paiement
        const [reponseGeneration] = await Promise.all([
            page.waitForResponse(
                (reponse) =>
                    reponse.url().includes("/generer-arrete-paiement") &&
                    reponse.request().method() === "POST",
            ),
            modaleArrete
                .getByRole("button", {
                    name: "Valider et éditer l'arrêté de paiement",
                    exact: true,
                })
                .click(),
        ]);
        expect(reponseGeneration.ok()).toBeTruthy();

        // Valider l'arrêté de paiement édité
        const [reponseInitiation] = await Promise.all([
            page.waitForResponse(
                (reponse) =>
                    reponse.url().includes("/initier-arrete-paiement") &&
                    reponse.request().method() === "POST",
            ),
            modaleArrete
                .getByRole("button", {
                    name: "Valider l'arrêté de paiement",
                    exact: true,
                })
                .click(),
        ]);
        expect(reponseInitiation.ok()).toBeTruthy();

        const corpsReponse = await reponseInitiation.json();
        expect(corpsReponse.etat.etat).toBe("OK_VERIFIE");

        // Après un rafraîchissement de la page, le dossier doit apparaître à l'état "Accepté - arrêté à signer"
        await page.reload();
        await page.waitForLoadState("networkidle");

        await expect(
            page.locator(".fr-badge--dossier-etat", {
                hasText: "Accepté - arrêté à signer",
            }),
        ).toBeVisible();
    } catch (e) {
        console.log("CAUGHT ERROR", e);
        await context.close();
    }
});
