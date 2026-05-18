import { DeclarerDeniDeJustice } from "@/apps/visiteur/components/DeclarerDeniDeJustice";
import { useVisiteurNavigate } from "@/apps/visiteur/routeur";
import { createFileRoute } from "@tanstack/react-router";
import * as React from "react";

function AccueilVisiteur() {
  const navigate = useVisiteurNavigate();
  return (
    <DeclarerDeniDeJustice onNext={() => navigate({ to: "/visiteur/test-eligibilite" })} />
  );
}

export const Route = createFileRoute("/visiteur/")({
  component: AccueilVisiteur,
});
