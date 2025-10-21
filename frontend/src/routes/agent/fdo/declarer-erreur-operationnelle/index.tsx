import { redirect, createFileRoute } from "@tanstack/react-router";

export const Route = createFileRoute(
  "/agent/fdo/declarer-erreur-operationnelle/",
)({
  loader: () =>
    redirect({
      to: "/agent/fdo/declarer-erreur-operationnelle/1-operation",
    }),
});
