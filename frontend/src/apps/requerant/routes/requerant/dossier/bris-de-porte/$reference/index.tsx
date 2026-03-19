import { NonTrouveComposant } from "@/apps/requerant/composants/routeur/NonTrouveComposant.tsx";
import { container } from "@/apps/requerant/container";
import { estDossierOkBrisDePorte } from "@/apps/requerant/formulaires/brisDePorte/1-bris-porte.schema.ts";
import { estDossierOkInfosRequerant } from "@/apps/requerant/formulaires/brisDePorte/2-infos-requerants.schema.ts";
import { RouteurRequerant } from "@/apps/requerant/routeur";
import { DossierManagerInterface } from "@/apps/requerant/services/DossierManager";
import { Loader } from "@/common/composants/Loader.tsx";
import {
  createFileRoute,
  notFound,
  NotFoundRouteProps,
  redirect,
} from "@tanstack/react-router";
import React from "react";

export const Route = createFileRoute(
  "/requerant/dossier/bris-de-porte/$reference/",
)({
  pendingComponent: Loader,
  notFoundComponent: (props: NotFoundRouteProps) => (
    <NonTrouveComposant {...props} />
  ),
  beforeLoad: async ({ params }) => {
    const dossier = await container
      .get<DossierManagerInterface>(DossierManagerInterface.$)
      .getDossier(params.reference);

    if (!dossier) {
      throw notFound({
        data: {
          titre: `Impossible de trouver le dossier ${params.reference}`,
          message: "Le dossier n'existe pas ou ne vous est pas accessible.",
        },
        throw: true,
      });
    }

    if (!estDossierOkBrisDePorte(dossier)) {
      return redirect<typeof RouteurRequerant>({
        to: "./1-bris-porte",
        params,
      });
    }

    if (!estDossierOkInfosRequerant(dossier)) {
      return redirect<typeof RouteurRequerant>({
        to: "./2-infos-requerant",
        params,
      });
    }

    return redirect<typeof RouteurRequerant>({
      to: "./3-pieces-jointes",
      params,
    });
  },
  shouldReload: true,
});
