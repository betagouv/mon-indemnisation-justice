#!/usr/bin/env node

import puppeteer from "puppeteer";

async function lireEntreeStandard() {
  return Buffer.concat(await process.stdin.toArray()).toString("utf8");
}

(async () => {
  const html = await lireEntreeStandard();

  if (!html.trim()) {
    throw new Error("Aucun contenu HTML reçu sur l'entrée standard");
  }

  const browser = await puppeteer.launch({
    headless: true,
    timeout: 30000,
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
      JSON.stringify({
        contenu: Buffer.from(pdf).toString("base64"),
      }),
    );
  } finally {
    await browser.close();
  }
})().catch((erreur) => {
  console.error(erreur);
  process.exit(1);
});
