import { createFileRoute, redirect } from "@tanstack/react-router";
import { RouteurRequerant } from "@/apps/requerant/routeur";

export const Route = createFileRoute("/requerant/dossier/bris-de-porte/$reference/")({
  beforeLoad: ({ params }) => {
    // TODO : vérifier que le dossier existe

    return redirect<typeof RouteurRequerant>({
      to: "./1-bris-porte",
      params,
    });
  },
});
