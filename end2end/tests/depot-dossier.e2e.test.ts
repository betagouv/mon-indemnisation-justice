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
    // Tenter de désactiver le cache https://stackoverflow.com/a/68650186/4558679
    await page.route('**', route => route.continue());

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

    // Étape 1 - infos relatives au bris de porte

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

    // Étape 2 - infos sur le requérant

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

    // Étape 3 - pièces jointes

    await expect(
        page.getByText("Déclarer un bris de porte", {exact: true}),
    ).toBeVisible();

    await expect(
        page.locator("h2", {
            hasText: "Documents à joindre à votre demande",
        }),
    ).toBeVisible();

    // Cliquer élément menu "Attestation complétée par les forces de l'ordres" puis bouton "Ajouter attestations complétées par les forces de l'ordres"
    await page.getByText("Photo de la porte endommagée").click();

    await page.locator("button", {hasText: "Ajouter des photos de la porte endommagée"}).click();

    // Sélectionner 1 fichier puis s'assurer que le type est déjà sélectionné puis valider
    await page
        .getByLabel("Documents à téléverser")
        .setInputFiles(
            path.join(
                __dirname,
                "./ressources/photo-porte-2.jpeg",
            ),
        );

    await page.locator("button", {hasText: "Prévisualiser",}).click();
    await expect(page.locator("h5", {hasText: "Fichier : photo-porte-2.jpeg"})).toBeVisible()
    await expect(page.getByLabel("Type de pièce jointe")).toHaveValue("photo_prejudice");

    await page.locator("button", {hasText: "Téléverser ce document",}).click();


    // Essayer de soumettre et s'assurer que l'alerte apparaît
    const urlCourante = page.url()

    await page.locator("button", {hasText: "Soumettre ma demande",}).click();

    expect(page.url()).toBe(urlCourante);

    await expect(page.locator("h3.fr-alert__title", {hasText: "Des documents sont manquants"})).toBeVisible();

    // Cliquer sur le bouton "Ajouter des documents" et sélectionner tous les fichiers requis restants
    await page.locator("button", {hasText: "Ajouter des documents",}).click();

    await page
        .getByLabel("Documents à téléverser")
        .setInputFiles(
            [
                path.join(
                    __dirname,
                    "./ressources/attestation_completee_par_les_forces_de_l_ordre.pdf",
                ),
                path.join(
                    __dirname,
                    "./ressources/cni.jpg",
                ),
                path.join(
                    __dirname,
                    "./ressources/facture-1.png",
                ),
                path.join(
                    __dirname,
                    "./ressources/rib.jpg",
                ),
                path.join(
                    __dirname,
                    "./ressources/acte_de_propriete.pdf",
                )
            ]
        );

    await page.locator("button", {hasText: "Prévisualiser",}).click();

    await expect(page.locator("h2.fr-stepper__title", {hasText: "Fichier n°1 : attestation_completee_par_les_forces_de_l_ordre.pdf"})).toBeVisible();
    await selectionnerMenu(page, "Type de pièce jointe", {label: "Attestation complétée par les forces de l'ordre"});
    await page.locator("button", {hasText: "Document suivant"}).click();

    await expect(page.locator("h2.fr-stepper__title", {hasText: "Fichier n°2 : cni.jpg"})).toBeVisible();
    await selectionnerMenu(page, "Type de pièce jointe", {label: "Pièce d'identité"});
    await page.locator("button", {hasText: "Document suivant"}).click();

    await expect(page.locator("h2.fr-stepper__title", {hasText: "Fichier n°3 : facture-1.png"})).toBeVisible();
    await selectionnerMenu(page, "Type de pièce jointe", {label: "Facture"});
    await page.locator("button", {hasText: "Document suivant"}).click();

    await expect(page.locator("h2.fr-stepper__title", {hasText: "Fichier n°4 : rib.jpg"})).toBeVisible();
    await selectionnerMenu(page, "Type de pièce jointe", {label: "RIB"});
    await page.locator("button", {hasText: "Document suivant"}).click();

    await expect(page.locator("h2.fr-stepper__title", {hasText: "Fichier n°5 : acte_de_propriete.pdf"})).toBeVisible();
    await selectionnerMenu(page, "Type de pièce jointe", {label: "Titre de propriété"});
    await page.locator("button", {hasText: "Téléverser ces documents"}).click();

    // Attendre que le téléversement se termine

    await page.waitForLoadState("networkidle");
    await expect(page.locator('dialog#ajouter-pieces-jointes-modale')).toBeHidden();

    // Soumettre la demande et arriver sur la page des demandes
    await page.locator("button", {hasText: "Soumettre ma demande",}).click();
    await expect(page).toHaveURL("/requerant/mes-demandes");

    // La modale de confirmation doit s'afficher
    await expect(page.locator("h3.fr-alert__title", {hasText: "Votre dossier a bien été déposé"})).toBeVisible();

    await page.locator("ul.fr-btns-group li button", {hasText: "Fermer",}).click();
    // Laissons le temps à la modale de disparaitre
    await page.waitForTimeout(250);
    await expect(page.locator('dialog#dossier-depose-modale')).toBeHidden();


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
