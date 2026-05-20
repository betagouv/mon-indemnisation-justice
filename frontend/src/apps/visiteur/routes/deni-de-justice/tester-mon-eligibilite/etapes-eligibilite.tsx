import { createFileRoute, Outlet } from "@tanstack/react-router";
import * as React from "react";

export const Route = createFileRoute(
  "/deni-de-justice/tester-mon-eligibilite/etapes-eligibilite",
)({
  component: () => <Outlet />,
});
