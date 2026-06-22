import { createFileRoute, Navigate } from "@tanstack/react-router";
import React from "react";

export const Route = createFileRoute("/agents/gestion/")({
  component: RouteComponent,
});

function RouteComponent() {
  return <Navigate to="./actifs" />;
}
