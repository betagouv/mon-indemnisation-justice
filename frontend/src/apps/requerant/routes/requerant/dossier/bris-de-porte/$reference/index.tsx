import { container } from "@/apps/requerant/container";
import { DossierManagerInterface } from "@/apps/requerant/services/DossierManager";
import { createFileRoute, notFound, redirect } from "@tanstack/react-router";
import { RouteurRequerant } from "@/apps/requerant/routeur";

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

    return redirect<typeof RouteurRequerant>({
      to: "./1-bris-porte",
      params,
    });
  },
  shouldReload: true,
});
