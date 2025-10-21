import React from "react";
import { createFileRoute } from "@tanstack/react-router";

export const Route = createFileRoute("/agent/fdo/foire-aux-questions")({
  component: () => (
    <div>
      <h1>Foire aux questions</h1>
    </div>
  ),
});
