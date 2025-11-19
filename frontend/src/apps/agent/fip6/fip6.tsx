import "@/apps/_init.ts";
import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import React, { JSX, StrictMode } from "react";
import {
  createRouter,
  LinkProps,
  RouterProvider,
} from "@tanstack/react-router";

import { routeTree } from "@/apps/agent/fip6/routeur/routeur-fip6.gen.ts";
import { container } from "@/common/services/agent";
import ReactDOM from "react-dom/client";
import { Provider } from "inversify-react";
import { AgentManagerInterface } from "@/common/services/agent/agent.ts";

import { startReactDsfr } from "@codegouvfr/react-dsfr/spa";
import { ColorScheme } from "@codegouvfr/react-dsfr/useIsDark";

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

// Création du router Tanstack
export const router = createRouter({
  routeTree,
  defaultPreload: "intent",
  defaultStaleTime: 5000,
  scrollRestoration: true,
  context: {
    agent: queryClient.fetchQuery({
      queryKey: ["moi-agent-fip6"],
      queryFn: () => container.get(AgentManagerInterface.$).moi(),
    }),
  },
});

declare module "@codegouvfr/react-dsfr/spa" {
  interface RegisterLink {
    Link: (props: LinkProps) => JSX.Element;
  }
}

const rootElement = document.getElementById("react-app")!;

if (!rootElement.innerHTML) {
  const root = ReactDOM.createRoot(rootElement);
  root.render(
    <StrictMode>
      <QueryClientProvider client={queryClient}>
        <Provider container={container}>
          <RouterProvider router={router} />
        </Provider>
      </QueryClientProvider>
    </StrictMode>,
  );
}
