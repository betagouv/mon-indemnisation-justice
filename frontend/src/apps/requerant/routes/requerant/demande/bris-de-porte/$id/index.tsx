import { createFileRoute, redirect } from "@tanstack/react-router";
import { RouteurRequerant } from "@/apps/requerant/routeur";

export const Route = createFileRoute("/requerant/demande/bris-de-porte/$id/")({
  loader: ({ cause }) => {
    // TODO : vérifier que le dossier existe

    console.log("Ici");

    if (cause === "enter") {
      return redirect<typeof RouteurRequerant>({ to: "1-etat-civil" });
    }
  },
});
