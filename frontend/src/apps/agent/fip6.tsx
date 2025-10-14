import { QueryClient, QueryClientProvider } from "@tanstack/react-query";
import React, { StrictMode } from "react";
import "@/apps/_init";
import { createRouter, RouterProvider } from "@tanstack/react-router";

import { routeTree } from "@/routes/agent-fip6.gen.ts";
import { container } from "@/common/services/agent";
import ReactDOM from "react-dom/client";
import { Provider } from "inversify-react";
import { AgentManagerInterface } from "@/common/services/agent/agent.ts";
import { Agent } from "@/common/models";

declare global {
  interface Window {
    dsfr: any;
  }
}
// Création du query client Tanstack
const queryClient = new QueryClient();

const moi = await container.get(AgentManagerInterface.$).moi();

// Création du router Tanstack
const router = createRouter({
  routeTree,
  defaultPreload: "intent",
  defaultStaleTime: 5000,
  scrollRestoration: true,
  context: {
    agent: moi,
  },
});

// Register things for typesafety
declare module "@tanstack/react-router" {
  interface Register {
    router: typeof router;
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
