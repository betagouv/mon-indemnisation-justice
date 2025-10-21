import React from "react";
import { createFileRoute } from "@tanstack/react-router";

export const Route = createFileRoute("/agent/fdo/")({
  component: RouteComponent,
});

function RouteComponent() {
  return (
    <div>
      <h1>Accueil</h1>
    </div>
  );
}
