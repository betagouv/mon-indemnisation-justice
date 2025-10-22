import { redirect, createFileRoute } from "@tanstack/react-router";

export const Route = createFileRoute("/agent/fdo/erreur-operationnelle/")({
  loader: () =>
    redirect({
      to: "/agent/fdo/erreur-operationnelle/nouvelle-declaration",
    }),
});
