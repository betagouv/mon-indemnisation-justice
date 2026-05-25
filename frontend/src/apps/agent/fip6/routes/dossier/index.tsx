import { createFileRoute, Navigate } from "@tanstack/react-router";
import React from "react";

const IndexDossier = () => <Navigate to={"/dossiers"} search={{} as any} />;
export const Route = createFileRoute("/dossier/")({
  component: IndexDossier,
});
