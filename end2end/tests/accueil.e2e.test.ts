import {expect, test} from "@playwright/test";

test("la tuile bris de porte mène à la page bris de porte", async ({page}) => {
    await page.goto("/");

    await expect(
        page.getByRole("heading", {name: "Bienvenue sur Mon Indemnisation Justice"}),
    ).toBeVisible();

    await page.getByRole("link", {name: "Déclarer un bris de porte"}).click();

    await expect(page).toHaveURL("/bris-de-porte/");
    await expect(
        page.getByRole("heading", {name: "Comment utiliser notre service en ligne ?"}),
    ).toBeVisible();
});
