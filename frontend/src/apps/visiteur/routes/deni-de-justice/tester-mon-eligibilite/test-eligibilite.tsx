import { TestEligibilite } from "@/apps/visiteur/components/TestEligibilite";
import { useVisiteurNavigate } from "@/apps/visiteur/routeur";
import { createFileRoute } from "@tanstack/react-router";
import * as React from "react";

function TestEligibiliteRoute() {
  const navigate = useVisiteurNavigate();
  return (
    <TestEligibilite
      onPrecedent={() => navigate({ to: "/deni-de-justice/tester-mon-eligibilite/" })}
      onCommencer={() => navigate({ to: "/deni-de-justice/tester-mon-eligibilite/etapes-eligibilite/1" as any })}
    />
  );
}

export const Route = createFileRoute("/deni-de-justice/tester-mon-eligibilite/test-eligibilite")({
  component: TestEligibiliteRoute,
});
