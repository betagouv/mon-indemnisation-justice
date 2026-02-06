import { createFileRoute, redirect } from "@tanstack/react-router";
import { RouteurRequerant } from "@/apps/requerant/routeur";

export const Route = createFileRoute("/requerant/")({
  loader: ({ cause, params }) => {
    if (cause === "enter") {
      // TODO si un dossier est en attente de finalisation, rediriger

      return redirect<typeof RouteurRequerant>({
        to: "/requerant/mes-demandes",
        params: {
          reference: (params as any).reference,
        },
      });
    }
  },
});
