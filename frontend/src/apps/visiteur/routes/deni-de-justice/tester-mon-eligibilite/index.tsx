import { DeclarerDeniDeJustice } from "@/apps/visiteur/components/DeclarerDeniDeJustice";
import { useVisiteurNavigate } from "@/apps/visiteur/routeur";
import { createFileRoute } from "@tanstack/react-router";
import * as React from "react";

function AccueilVisiteur() {
  const navigate = useVisiteurNavigate();
  return (
    <DeclarerDeniDeJustice
      onNext={() => navigate({ to: "/deni-de-justice/tester-mon-eligibilite/test-eligibilite" })}
    />
  );
}

export const Route = createFileRoute("/deni-de-justice/tester-mon-eligibilite/")({
  component: AccueilVisiteur,
});
