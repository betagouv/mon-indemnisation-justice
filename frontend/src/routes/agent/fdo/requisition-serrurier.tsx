import React from "react";
import { createFileRoute } from "@tanstack/react-router";

export const Route = createFileRoute("/agent/fdo/requisition-serrurier")({
  component: () => (
    <div>
      <h1>Bienvenue</h1>
    </div>
  ),
});

function AccueilComponent() {
  return (
    <div>
      <h1>Bienvenue</h1>
    </div>
  );
}
