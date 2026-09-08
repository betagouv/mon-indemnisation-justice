import { createFileRoute } from "@tanstack/react-router";
import React from "react";

export const Route = createFileRoute("/dysfonctionnement/s-identifier/usagers")(
  {
    component: RouteComponent,
  },
);

function RouteComponent() {
  return <div>Hello "/dysfonctionnement/s-identifier/avocats"!</div>;
}
