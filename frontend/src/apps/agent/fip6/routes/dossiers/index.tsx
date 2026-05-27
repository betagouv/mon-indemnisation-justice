import "@/style/agents.css";
import { createFileRoute, Navigate } from "@tanstack/react-router";
import React from "react";

export const Route = createFileRoute("/dossiers/")({
  component: IndexDossiers,
});

function IndexDossiers() {
  return <Navigate to="/dossiers/rechercher" search={{} as any} />;
}
