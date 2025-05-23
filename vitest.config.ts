import * as path from "node:path";
import { configDefaults } from "vitest/config";
import { defineConfig } from "vitest/config";

export default defineConfig({
  test: {
    include: ["assets/**/*.test.ts"],
    exclude: [...configDefaults.exclude, "assets/tests/e2e"],
  },
  resolve: {
    alias: {
      "@": path.resolve(__dirname, "./assets"),
    },
  },
});
