import { ContexteUsager } from "@/apps/requerant/routeur/contexte.ts";
import { UsagerManagerInterface } from "@/apps/requerant/services/UsagerManager.ts";
import * as Sentry from "@sentry/browser";
import { createRouter } from "@tanstack/react-router";
import { Crisp } from "crisp-sdk-web";
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
    Sentry.setTag("app", "requerant");
    Sentry.setUser({
      id: context.usager.id,
      email: context.usager.courriel,
      username: context.usager.nom,
    });
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
      // Route vers @./../routes/dossiers/usager/$uid.tsx
      url_dossier: `${window.location.origin}/agent/fip6/dossiers/usager/${context.usager.id}`,
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
