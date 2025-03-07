#!/usr/bin/env node

import puppeteer from "puppeteer";
import path from "path";
import fs from "fs";
import { fileURLToPath } from "url";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

(async () => {
  const browser = await puppeteer.launch({
    args: ["--no-sandbox"],
  });
  const page = await browser.newPage();

  console.log(process.argv.at(-2), process.argv.at(-1));

  //await page.setContent(fs.readFileSync(process.argv.at(-2), "utf8"));

  await page.goto(`file://${process.argv.at(-2)}`, {
    waitUntil: "networkidle0",
  });

  await page.pdf({
    path: process.argv.at(-1),
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
})();
