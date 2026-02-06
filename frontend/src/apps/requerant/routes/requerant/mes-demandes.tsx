import React from "react";
import { createFileRoute } from "@tanstack/react-router";

export const Route = createFileRoute("/requerant/mes-demandes")({
  component: RouteComponent,
});

function RouteComponent() {
  return (
    <div>
      <h1>Mes demandes</h1>
    </div>
  );
}
