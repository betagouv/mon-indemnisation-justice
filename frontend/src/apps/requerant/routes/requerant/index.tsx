import { createFileRoute, redirect } from "@tanstack/react-router";
import { RouteurRequerant } from "@/apps/requerant/routeur";

export const Route = createFileRoute("/requerant/")({
  loader: ({ cause, params }) => {
    if (cause === "enter") {
      return redirect<typeof RouteurRequerant>({
        to: "/requerant/mes-demandes",
      });
    }
  },
});
