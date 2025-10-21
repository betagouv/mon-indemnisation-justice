import { rootRoute, physical } from "@tanstack/virtual-file-routes";

export const routes = rootRoute("root.tsx", [
  physical("/agents", "agent/fip6/agents"),
  physical("/dossiers", "agent/fip6/dossiers"),
]);
