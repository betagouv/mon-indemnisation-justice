import React from "react";
import { createFileRoute } from "@tanstack/react-router";

export const Route = createFileRoute("/agent/fdo/declarations")({
  component: () => (
    <div>
      <h1>Mes déclarations</h1>
    </div>
  ),
});
