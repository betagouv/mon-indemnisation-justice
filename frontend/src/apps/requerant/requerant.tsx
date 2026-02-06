import "@/apps/_init.ts";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import React, { JSX, StrictMode } from "react";
import {
  createRouter,
  LinkProps,
  RouterProvider,
} from "@tanstack/react-router";

import { routeTree as requerantRouteTree } from "@/apps/requerant/routeur/routeur-requerant.gen.ts";
import { container } from "@/apps/requerant/container.ts";
import ReactDOM from "react-dom/client";
import { Provider } from "inversify-react";

import { startReactDsfr } from "@codegouvfr/react-dsfr/spa";
import { ColorScheme } from "@codegouvfr/react-dsfr/useIsDark";
import { UsagerManagerInterface } from "@/apps/requerant/services/UsagerManager.ts";
import { ContexteUsager } from "@/apps/requerant/routeur/contexte.ts";

startReactDsfr({
  defaultColorScheme:
    (localStorage.getItem("scheme") as ColorScheme) ?? "system",
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

// Création du router Tanstack

container
  .get(UsagerManagerInterface.$)
  .moi()
  .then((context: ContexteUsager) => {
    const router = createRouter({
      routeTree: requerantRouteTree,
      defaultPreload: "intent",
      defaultStaleTime: 5000,
      scrollRestoration: true,
      context,
    });

    const root = ReactDOM.createRoot(document.body);
    root.render(
      <StrictMode>
        <QueryClientProvider client={queryClient}>
          <Provider container={container}>
            <RouterProvider router={router} />
          </Provider>
        </QueryClientProvider>
      </StrictMode>,
    );
  });
