import { rootRoute, physical } from "@tanstack/virtual-file-routes";

export const routes = rootRoute("agent/fip6/root.tsx", [
  physical("/agent/fip6/agents", "agent/fip6/agents"),
  physical("/agent/fip6/dossiers", "agent/fip6/dossiers"),
]);
