import { TestEligibilite } from "@/apps/visiteur/components/TestEligibilite";
import { useVisiteurNavigate } from "@/apps/visiteur/routeur";
import { createFileRoute } from "@tanstack/react-router";
import * as React from "react";

function TestEligibiliteRoute() {
  const navigate = useVisiteurNavigate();
  return (
    <TestEligibilite
      onPrecedent={() => navigate({ to: "/visiteur/" })}
      onCommencer={() => navigate({ to: "/visiteur/etapes-eligibilite" })}
    />
  );
}

export const Route = createFileRoute("/visiteur/test-eligibilite")({
  component: TestEligibiliteRoute,
});
