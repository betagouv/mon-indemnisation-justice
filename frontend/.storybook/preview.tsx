// @ts-expect-error — import CSS Vite valide, pas de types pour ce sous-chemin de package
import "@codegouvfr/react-dsfr/main.css";
import { startReactDsfr } from "@codegouvfr/react-dsfr/spa";
import type { Preview } from "@storybook/react";
import React from "react";

startReactDsfr({
  defaultColorScheme: "system",
  Link: ({ href, children, ...props }) => (
    <a href={href} {...props}>
      {children}
    </a>
  ),
});

const preview: Preview = {
  parameters: {
    controls: {
      matchers: {
        color: /(background|color)$/i,
        date: /Date$/i,
      },
    },
  },
};

export default preview;
