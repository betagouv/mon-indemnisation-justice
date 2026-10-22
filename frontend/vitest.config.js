/// <reference types="./types" />

import { fileURLToPath, URL } from "node:url";
import { defineConfig } from "vitest/config";

export default defineConfig({
  test: {
    include: ["tests/**/*.test.ts"],
  },
  // Vite 8 transpile via Oxc (et non plus esbuild) : les décorateurs "legacy" (class-validator, inversify, ...) doivent
  // être explicitement activés, `tests/` n'étant pas couvert par le `tsconfig.json`
  oxc: {
    decorator: {
      legacy: true,
      emitDecoratorMetadata: true,
    },
  },
  resolve: {
    alias: {
      "@": fileURLToPath(new URL("./src", import.meta.url)),
      "@common": fileURLToPath(new URL("./src/common", import.meta.url)),
      "@fdo": fileURLToPath(new URL("./src/apps/agent/fdo", import.meta.url)),
      "@fip6": fileURLToPath(new URL("./src/apps/agent/fip6", import.meta.url)),
    },
  },
});
