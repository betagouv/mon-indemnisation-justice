import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import React, { JSX, StrictMode } from "react";
import "@/apps/_init";
import { createRouter, RouterProvider } from "@tanstack/react-router";

import { routeTree } from "@/routers/generated/router-agent-fdo.gen.ts";
import { Link, type LinkProps } from "@tanstack/react-router";
import { container } from "@/common/services/agent";
import { createRoot } from "react-dom/client";
import { Provider } from "inversify-react";
import { AgentManagerInterface } from "@/common/services/agent/agent.ts";
import { startReactDsfr } from "@codegouvfr/react-dsfr/spa";
import { ColorScheme } from "@codegouvfr/react-dsfr/useIsDark";

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

// Création du router Tanstack
const routerFDO = createRouter({
  routeTree,
  defaultPreload: "intent",
  defaultStaleTime: 5000,
  scrollRestoration: true,
  context: {
    agent: queryClient.fetchQuery({
      queryKey: ["moi-agent-fdo"],
      queryFn: () => container.get(AgentManagerInterface.$).moi(),
    }),
  },
});

declare module "@codegouvfr/react-dsfr/spa" {
  interface RegisterLink {
    Link: (props: LinkProps) => JSX.Element;
  }
}

// Register things for typesafety
declare module "@tanstack/react-router" {
  interface Register {
    // @ts-ignore
    router: typeof routerFDO;
  }
}

createRoot(document.body).render(
  <StrictMode>
    <QueryClientProvider client={queryClient}>
      <Provider container={container}>
        <RouterProvider router={routerFDO} />
      </Provider>
    </QueryClientProvider>
  </StrictMode>,
);
