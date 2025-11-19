import { redirect, createFileRoute } from "@tanstack/react-router";

export const Route = createFileRoute("/agent/fdo/erreur-operationnelle/")({
  loader: ({ cause, params }) => {
    if (cause === "enter") {
      throw redirect({
        to: "/agent/fdo/erreur-operationnelle/$reference/1-operation",
        params: {
          reference: (params as any).reference,
        },
      });
    }
  },
});
