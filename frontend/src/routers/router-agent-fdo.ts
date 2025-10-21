import { rootRoute, physical } from "@tanstack/virtual-file-routes";

export const routes = rootRoute("root-fdo.tsx", [
  physical("/agent/fdo", "agent/fdo"),
]);
