// eslint.config.js
import { defineConfig } from "eslint/config";

export default defineConfig([
  {
    rules: {
      "react/no-unescaped-entities": "off",
      "max-len": ["error", { code: 120, tabWidth: 4 }],
    },
  },
]);
