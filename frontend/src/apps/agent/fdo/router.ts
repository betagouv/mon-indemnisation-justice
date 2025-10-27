import { routeTree } from "@/routers/generated/router-agent-fdo.gen.ts";
import { type LinkProps } from "@tanstack/react-router";
import { queryClient } from "./query-client.ts";
import { container } from "./services/";
import { createRouter } from "@tanstack/react-router";
import { AgentManagerInterface } from "@/common/services/agent/agent.ts";
import { JSX } from "react";

// Création du router Tanstack
export const router = createRouter({
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
    router: typeof router;
  }
}
