import { ContexteUsager } from "@/apps/requerant/routeur/contexte.ts";
import { UsagerManagerInterface } from "@/apps/requerant/services/UsagerManager.ts";
import { createRouter } from "@tanstack/react-router";
import { container } from "../container";
import { routeTree } from "./routeur-requerant.gen";

export type ErreurResourceInconnue = {
  titre?: string;
  message: string;
};

// Création du router Tanstack
let RouteurRequerant;
await container
  .get(UsagerManagerInterface.$)
  .moi()
  .then((context: ContexteUsager) => {
    RouteurRequerant = createRouter({
      routeTree,
      defaultPreload: "intent",
      defaultStaleTime: 5000,
      scrollRestoration: true,
      context,
    });
  });

declare module "@tanstack/react-router" {
  interface Register {
    router: typeof RouteurRequerant;
  }

  interface NotFoundErrorData {
    data: ErreurResourceInconnue | any;
  }

  interface Location {
    state: {
      session: any;
    };
  }
}

export { RouteurRequerant };
