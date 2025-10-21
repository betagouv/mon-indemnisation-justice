import React from "react";
import { createFileRoute } from "@tanstack/react-router";

export const Route = createFileRoute("/agent/fdo/")({
  component: RouteComponent,
});

function RouteComponent() {
  return <div>Bonjour agent</div>;
}
