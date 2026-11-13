#!/usr/bin/env node

import puppeteer from "puppeteer";
import fs from "fs";

const source = process.argv.at(-2);
const destination = process.argv.at(-1);

(async () => {
  const browser = await puppeteer.launch({
    headless: true,
    timeout: 30000,
    browser: "firefox",
  });
  const page = await browser.newPage();

  await page.goto(`file://${source}`, {
    waitUntil: "domcontentloaded",
    timeout: 0,
  });

  const res = await page.pdf({
    path: destination,
    displayHeaderFooter: false,
    headerTemplate: "",
    footerTemplate: "",
    printBackground: true,
    omitBackground: false,
    format: "A4",
    // Avec Firefox (WebDriver BiDi), puppeteer envoie `shrinkToFit: !preferCSSPageSize` : sans cette option, le moindre
    // débordement horizontal fait réduire toute la page (et donc la police). On s'appuie sur la règle `@page` du
    // template (A4, marges en cm) et on impose une échelle de 1.
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
  await browser.close();

  if (!fs.existsSync(destination)) {
    throw new Error(`No such file ${destination}`);
  }

  return 0;
})();
