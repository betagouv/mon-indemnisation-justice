import "@/apps/_init.ts";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import React, { JSX, StrictMode } from "react";
import { Link, LinkProps, RouterProvider } from "@tanstack/react-router";

import { container } from "@/apps/requerant/container.ts";
import ReactDOM from "react-dom/client";
import { Provider } from "inversify-react";

import { startReactDsfr } from "@codegouvfr/react-dsfr/spa";
import { ColorScheme } from "@codegouvfr/react-dsfr/useIsDark";
import { RouteurRequerant } from "@/apps/requerant/routeur";

startReactDsfr({
  defaultColorScheme:
    (localStorage.getItem("scheme") as ColorScheme) ?? "system",
  Link,
});

declare global {
  interface Window {
    dsfr: any;
  }
}
// Création du query client Tanstack
const queryClient = new QueryClient();

declare module "@codegouvfr/react-dsfr/spa" {
  interface RegisterLink {
    Link: (props: LinkProps) => JSX.Element;
  }
}

const root = ReactDOM.createRoot(document.body);
root.render(
  <StrictMode>
    <QueryClientProvider client={queryClient}>
      <Provider container={container}>
        <RouterProvider router={RouteurRequerant} />
      </Provider>
    </QueryClientProvider>
  </StrictMode>,
);
