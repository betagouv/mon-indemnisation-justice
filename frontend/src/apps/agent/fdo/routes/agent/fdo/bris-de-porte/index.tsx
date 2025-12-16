import { redirect, createFileRoute } from "@tanstack/react-router";

export const Route = createFileRoute("/agent/fdo/bris-de-porte/")({
  loader: ({ cause, params }) => {
    if (cause === "enter") {
      throw redirect({
        to: "/agent/fdo/bris-de-porte/$reference/1-bris-de-porte",
        params: {
          reference: (params as any).reference,
        },
      });
    }
  },
});
