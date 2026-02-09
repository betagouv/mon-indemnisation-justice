import { createFileRoute } from "@tanstack/react-router";
import React from "react";

export const Route = createFileRoute("/requerant/demande/bris-de-porte/")({
  component: RouteComponent,
});

function RouteComponent() {
  return <div>Hello "/requerant/dossier/bris-de-porte/"!</div>;
}
