import { container } from "@/apps/requerant/container";
import { estDossierOkBrisDePorte } from "@/apps/requerant/formulaires/brisDePorte/1-bris-porte.schema.ts";
import { estDossierOkInfosRequerant } from "@/apps/requerant/formulaires/brisDePorte/2-infos-requerants.schema.ts";
import { estDossierOkPiecesJointes } from "@/apps/requerant/formulaires/brisDePorte/3-pieces-jointes.schema.ts";
import { RouteurRequerant } from "@/apps/requerant/routeur";
import { DossierManagerInterface } from "@/apps/requerant/services/DossierManager";
import { Loader } from "@/common/composants/Loader.tsx";
import { createFileRoute, notFound, redirect } from "@tanstack/react-router";

export const Route = createFileRoute("/requerant/dossier/bris-de-porte/$id/")({
  pendingComponent: Loader,
  beforeLoad: async ({ params }) => {
    const dossier = await container
      .get<DossierManagerInterface>(DossierManagerInterface.$)
      .getDossier(parseInt(params.id));

    if (!dossier) {
      throw notFound({
        data: {
          titre: `Impossible de trouver le dossier ${params.id}`,
          message: "Le dossier n'existe pas ou ne vous est pas accessible.",
        },
        throw: true,
      });
    }

    if (dossier.estBrouillon && !estDossierOkBrisDePorte(dossier)) {
      return redirect<typeof RouteurRequerant>({
        to: "./1-bris-porte",
        params,
      });
    }

    if (dossier.estBrouillon && !estDossierOkInfosRequerant(dossier)) {
      return redirect<typeof RouteurRequerant>({
        to: "./2-infos-requerant",
        params,
      });
    }

    if (dossier.estBrouillon && !estDossierOkPiecesJointes(dossier)) {
      redirect<typeof RouteurRequerant>({
        to: "./3-pieces-jointes",
        params,
      });
    }
    return redirect<typeof RouteurRequerant>({
      to: "./4-recapitulatif",
      params,
    });
  },
  shouldReload: true,
});
