#!/usr/bin/env node

import puppeteer from "puppeteer";
import {PDFArray, PDFDict, PDFDocument, PDFHexString, PDFName, PDFString} from 'pdf-lib';
import {parseArgs} from "node:util";

/**
 * Les options :
 * * `--timeout=<secondes>` : délai maximal, en secondes, accordé à chaque étape de l'impression (ouverture de la page,
 * impression PDF et extraction de la position des objets). `10` par défaut.
 * * `--extraire-objets` : si présent, extraire la position des objets sur le document PDF produit. `false` par défaut.
 */
function lireOptions() {
  const {values} = parseArgs({
    options: {
      timeout: {type: "string", default: "10"},
      "extraire-objects": {type: "boolean", default: false},
    },
  });

  const secondes = Number(values.timeout);

  if (!Number.isFinite(secondes) || secondes <= 0) {
    throw new Error(`Option --timeout invalide : "${values.timeout}" (nombre de secondes strictement positif attendu)`);
  }

  return {
    timeout: secondes * 1000,
    extraireObjets: values["extraire-objects"],
  };
}

async function lireEntreeStandard() {
  return Buffer.concat(await process.stdin.toArray()).toString("utf8");
}

async function extraireContenuEtObjets(pdf, prefix = 'objet://') {
  const documentPDF = await PDFDocument.load(pdf);
  const boxes = [];

  // Pour chaque page du document PDF, on recherche les liens dont la cible correspond au préfixe `prefix`
  documentPDF.getPages().forEach((page, pageIndex) => {
    const annots = page.node.lookupMaybe(PDFName.of('Annots'), PDFArray);
    if (!annots) return;
    const pageHeight = page.getHeight();

    // Itération inversée pour pouvoir supprimer sans risque
    for (let i = annots.size() - 1; i >= 0; i--) {
      const annot = annots.lookup(i, PDFDict);
      const action = annot.lookupMaybe(PDFName.of('A'), PDFDict);
      const uriObj = action?.lookup(PDFName.of('URI'));
      if (!(uriObj instanceof PDFString || uriObj instanceof PDFHexString)) continue;

      const uri = uriObj.decodeText();
      if (!uri.startsWith(prefix)) continue;

      const [x1, y1, x2, y2] = annot
        .lookup(PDFName.of('Rect'), PDFArray)
        .asArray()
        .map(n => n.asNumber());

      boxes.push([
          uri.slice(prefix.length),
          {
            // La numérotation commence à "1"
            page: pageIndex + 1,
            x: Math.min(x1, x2),
            y: pageHeight - Math.max(y1, y2),
            width: x2 - x1,
            height: y2 - y1,
            // Hauteur de la page, pour les prestataires qui placent l'origine en bas à gauche (repère PDF natif)
            pageHeight,
          }
      ]);

      annots.remove(i); // Supprimer le faux lien pour éviter des incohérences d'affichage
    }
  });

  // Sérialiser le PDF modifié (sans les faux liens) avant de l'encoder
  const contenuPDF = await documentPDF.save();

  return {
    contenu: Buffer.from(contenuPDF).toString("base64"),
    objets: Object.fromEntries(boxes),
  };
}

(async () => {
  const {timeout: delai, extraireObjets} = lireOptions();
  const html = await lireEntreeStandard();

  if (!html.trim()) {
    throw new Error("Aucun contenu HTML reçu sur l'entrée standard");
  }

  const browser = await puppeteer.launch({
    headless: true,
    timeout: delai,
    protocolTimeout: delai,
    browser: "firefox",
  });

  try {
    const page = await browser.newPage();

    await page.setContent(html, {
      waitUntil: "domcontentloaded",
      timeout: 0,
    });

    const pdf = await page.pdf({
      displayHeaderFooter: false,
      headerTemplate: "",
      footerTemplate: "",
      printBackground: true,
      omitBackground: false,
      timeout: delai,
      format: "A4",
      preferCSSPageSize: true,
      scale: 1,
      margin: {
        bottom: 0,
        left: 0,
        right: 0,
        top: 0,
      },
    });

    await page.close();

    process.stdout.write(
      JSON.stringify(
        extraireObjets
          ? await extraireContenuEtObjets(pdf)
          : {contenu: Buffer.from(pdf).toString("base64")}
      ),
    );
  } finally {
    await browser.close();
  }
})().catch((erreur) => {
  console.error(erreur);
  process.exit(1);
});
