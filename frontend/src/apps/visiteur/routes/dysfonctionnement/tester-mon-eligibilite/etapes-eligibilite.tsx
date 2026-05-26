import { createFileRoute, Outlet } from "@tanstack/react-router";
import * as React from "react";

export const Route = createFileRoute(
  "/dysfonctionnement/tester-mon-eligibilite/etapes-eligibilite",
)({
  component: () => <Outlet />,
});
