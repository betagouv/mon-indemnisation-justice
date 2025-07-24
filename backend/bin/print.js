#!/usr/bin/env node

import puppeteer from "puppeteer";
import fs from "fs";

const source = process.argv.at(-2);
const destination = process.argv.at(-1);
const chromePath = process.env.CHROMIUM_PATH;

(async () => {
  const browser = await puppeteer.launch({
    args: [
      "--no-sandbox",
      "--disable-gpu",
      "--disable-translate",
      "--disable-extensions",
    ],
    headless: "new",
    timeout: 30000,
    executablePath: chromePath,
    browser: "chrome",
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
    printBackground: false,
    format: "A4",
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
