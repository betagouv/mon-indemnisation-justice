import { container } from "@/apps/requerant/container.ts";
import { RouteurRequerant } from "@/apps/requerant/routeur";
import { DossierManagerInterface } from "@/apps/requerant/services/DossierManager.ts";
import { createFileRoute, redirect } from "@tanstack/react-router";
import { DossierApercu } from "@/apps/requerant/models/Dossier.ts";

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
        to: "/requerant/dossier/bris-de-porte/$reference/1-bris-porte",
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
