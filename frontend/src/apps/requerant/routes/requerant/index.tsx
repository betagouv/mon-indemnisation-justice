import { container } from "@/apps/requerant/container.ts";
import { DossierApercu } from "@/apps/requerant/models/Dossier.ts";
import { RouteurRequerant } from "@/apps/requerant/routeur";
import { DossierManagerInterface } from "@/apps/requerant/services/DossierManager.ts";
import { createFileRoute, redirect } from "@tanstack/react-router";

export const Route = createFileRoute("/requerant/")({
  loader: async ({ cause }) => {
    const mesDemandes = await container
      .get<DossierManagerInterface>(DossierManagerInterface.$)
      .mesDemandes();

    const dossierAFinalisers = mesDemandes.filter(
      (dossier: DossierApercu) => dossier.etatActuel.etat.type == "A_COMPLETER",
    );
    if (dossierAFinalisers.length === 1) {
      return redirect<typeof RouteurRequerant>({
        to: "/requerant/dossier/bris-de-porte/$id/1-bris-porte",
        params: {
          reference: (dossierAFinalisers.at(0) as DossierApercu).reference,
        },
      });
    }

    return redirect<typeof RouteurRequerant>({
      to: "/requerant/mes-demandes",
    });
  },
});
