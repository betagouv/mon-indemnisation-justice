import { createFileRoute, redirect } from "@tanstack/react-router";

export const Route = createFileRoute("/bris-de-porte/")({
  loader: ({ cause, params }) => {
    if (cause === "enter") {
      throw redirect({
        to: "/bris-de-porte/$id/1-bris-de-porte",
        params,
      });
    }
  },
});
