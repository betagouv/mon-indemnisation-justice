import { ContexteUsager } from "@/apps/requerant/routeur/contexte.ts";
import { UsagerManagerInterface } from "@/apps/requerant/services/UsagerManager.ts";
import { createRouter } from "@tanstack/react-router";
import { container } from "../container";
import { routeTree } from "./routeur-requerant.gen";
import { Crisp } from "crisp-sdk-web";

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
    Crisp.user.setEmail(context.usager.courriel);
    Crisp.user.setPhone(context.usager.telephone);
    Crisp.user.setNickname(context.usager.courriel);

    Crisp.session.setData({
      usager_id: context.usager.id,
    });
  });

declare module "@tanstack/react-router" {
  interface Register {
    router: typeof RouteurRequerant;
  }

  interface NotFoundErrorData {
    data: ErreurResourceInconnue | any;
  }

  interface HistoryState {
    flash?: {
      type: "success" | "error" | "warning" | "info";
      titre: string;
      message: string | string[];
    };
  }

  interface Location {
    state: {
      session: any;
    };
  }
}

export { RouteurRequerant };
