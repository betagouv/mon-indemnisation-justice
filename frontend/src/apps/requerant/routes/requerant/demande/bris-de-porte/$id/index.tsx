import { createFileRoute, redirect } from "@tanstack/react-router";
import { RouteurRequerant } from "@/apps/requerant/routeur";

export const Route = createFileRoute("/requerant/demande/bris-de-porte/$id/")({
  beforeLoad: ({ params }) => {
    // TODO : vérifier que le dossier existe
    console.log("Ici");

    return redirect<typeof RouteurRequerant>({
      to: "/requerant/demande/bris-de-porte/$id/1-etat-civil",
      params,
    });
  },
});
