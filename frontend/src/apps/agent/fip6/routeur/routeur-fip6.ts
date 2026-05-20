import { physical, rootRoute } from "@tanstack/virtual-file-routes";

export const routes = rootRoute("root-fip6.tsx", [
  physical("/agent/fip6/agents", "agents"),
  physical("/agent/fip6/dossiers", "dossiers"),
  physical("/agent/fip6/dossier", "dossier"),
]);
