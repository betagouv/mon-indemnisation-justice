import { createFileRoute, redirect } from "@tanstack/react-router";
import { RouteurRequerant } from "@/apps/requerant/routeur";

export const Route = createFileRoute("/requerant/")({
  loader: ({ cause, params }) => {
    return redirect<typeof RouteurRequerant>({
      to: "/requerant/mes-demandes",
    });
  },
});
