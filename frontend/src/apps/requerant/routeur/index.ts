import { routeTree } from "./routeur-requerant.gen";
import { container } from "../container";
import { createRouter } from "@tanstack/react-router";
import { UsagerManagerInterface } from "@/apps/requerant/services/UsagerManager.ts";
import { ContexteUsager } from "@/apps/requerant/routeur/contexte.ts";

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
}

export { RouteurRequerant };
