import {expect, test} from "@playwright/test";
import {formatYmd, selectionnerBoutonRadio} from "./helpers";
import {dateIlYaNJours} from "./helpers/date";
import {remplirChamp} from "./helpers/formulaire";

const clicSuivant = (page: import("@playwright/test").Page) =>
    page.getByRole("button", {name: /Étape suivante|Voir le résultat/}).click();

test.describe("inscription avocat", () => {
    test.beforeEach(async ({context}) => {
        // Démarrer une session incognito pour éviter les effets de bord des sessions en cookie
        await context.clearCookies();
    });

    test("test d'éligibilité jusqu'à l'inscription avocat", async ({page}) => {
        // Parcours à travers 8 pages distinctes (contrairement au test bris-de-porte, à révélation
        // progressive sur une seule page) : le timeout par défaut de la config (10s) est trop court.
        test.setTimeout(30000);

        await page.goto("/dysfonctionnement/tester-mon-eligibilite/");
        await page.getByText("Tester mon éligibilité à l'indemnisation").click();
        await expect(page).toHaveURL("/dysfonctionnement/tester-mon-eligibilite/test-eligibilite");

        await selectionnerBoutonRadio(page, "La juridiction concernée a-t-elle rendu sa décision ?", "Oui");
        await clicSuivant(page);

        await expect(page).toHaveURL(/1-date-decision/);
        // Date relative (6 mois) plutôt qu'absolue : la prescription expire au 1er janvier
        // de l'année décision+5, une date fixe finirait par se prescrire avec le temps.
        await page.locator('input[type="date"]').fill(formatYmd(dateIlYaNJours(180)));
        await clicSuivant(page);

        await expect(page).toHaveURL(/2-action-contentieuse/);
        await selectionnerBoutonRadio(page, "Avez-vous engagé une action contentieuse", "Non, aucune action contentieuse");
        await clicSuivant(page);

        await expect(page).toHaveURL(/3-type-decision/);
        await selectionnerBoutonRadio(page, "De quelles décisions disposez-vous ?", "Décision de première instance");
        await clicSuivant(page);

        await expect(page).toHaveURL(/4-pieces-procedure/);
        await selectionnerBoutonRadio(
            page,
            "De quelles pièces de la procédure disposez-vous ?",
            "Acte introductif de la procédure (requête, assignation, citation, déclaration d'appel, etc.)",
        );
        await clicSuivant(page);

        await expect(page).toHaveURL(/5-diligences/);
        await selectionnerBoutonRadio(
            page,
            "Disposez-vous de justificatifs des démarches que vous avez effectuées auprès de la juridiction ?",
            "Oui, j'ai des preuves de mes démarches",
        );
        await clicSuivant(page);

        await expect(page).toHaveURL(/resultat/);
        await page.getByText("Déposer un dossier").click();

        await expect(page).toHaveURL(/inscription/);

        // Questionnaire à tiroir : avocat, pas encore inscrit
        await selectionnerBoutonRadio(page, "Quel est votre rapport avec la procédure ?", "Je suis avocat(e)");
        await selectionnerBoutonRadio(page, "Êtes-vous déjà inscrit(e) sur la plateforme ?", "Non");

        // Identification par recherche dans l'annuaire (AvocatFixture : Jean MICHON, CNBF 123456, Barreau de Paris)
        await page.getByPlaceholder("Ex : Dupont").fill("Michon");
        await page.getByText(/MICHON Jean/).click();

        // Le formulaire n'apparaît qu'une fois l'avocat identifié — nom/prénom/barreau/CNBF sont
        // déjà pré-remplis par la sélection, il ne reste que les coordonnées et le mot de passe.
        const courriel = `avocat.e2e+${Date.now()}@courriel.fr`;

        await selectionnerBoutonRadio(page, "Civilité", "Monsieur");
        await remplirChamp(page, "Adresse email professionnelle", courriel);
        await remplirChamp(page, "Téléphone professionnel", "0612345678");
        await page.getByLabel("Mot de passe").first().fill("P4ssword");
        await remplirChamp(page, "Confirmation du mot de passe", "P4ssword");
        await page.getByLabel("J'accepte les conditions générales d'utilisation").check();

        await page.getByRole("button", {name: "S'inscrire"}).click();

        await expect(page.getByText("Vérifiez votre boîte email")).toBeVisible();
    });
});
