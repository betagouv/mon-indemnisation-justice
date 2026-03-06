import { container } from "@/apps/requerant/container";
import { DossierManagerInterface } from "@/apps/requerant/services/DossierManager";
import { createFileRoute, notFound, redirect } from "@tanstack/react-router";
import { RouteurRequerant } from "@/apps/requerant/routeur";
import { estDossierOkBrisDePorte } from "@/apps/requerant/formulaires/brisDePorte/1-bris-porte.schema.ts";
import { estDossierOkInfosRequerant } from "@/apps/requerant/formulaires/brisDePorte/2-infos-requerants.schema.ts";

export const Route = createFileRoute(
  "/requerant/dossier/bris-de-porte/$reference/",
)({
  beforeLoad: async ({ params }) => {
    const dossier = await container
      .get<DossierManagerInterface>(DossierManagerInterface.$)
      .getDossier(params.reference);

    if (!dossier) {
      console.log("Not found");
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
        to: "./2-infos-requerants",
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
