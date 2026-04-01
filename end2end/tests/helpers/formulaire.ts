import {Page} from "playwright-core";

export const selectionnerBoutonRadio = async (page: Page, question: string, choix: string): Promise<void> => {
    await page
        .locator("fieldset", {
            has: page.getByText(question, {exact: false}),
        })
        .getByLabel(choix)
        .check();
}

export const remplirChamp = async (page: Page, intitule: string, valeur: string): Promise<void> => {
    await page.getByLabel(intitule, {exact: false}).fill(valeur);
}

export const selectionnerMenu = async (page: Page, intitule: string, choix: string | null | {
    label: string
}): Promise<void> => {
    await page.getByLabel(intitule, {exact: false}).selectOption(choix);
}