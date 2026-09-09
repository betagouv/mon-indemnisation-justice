import { createRouter } from "@tanstack/react-router";
import { routeTree } from "./routeur-public.gen";

export const REQUERANT_URL = "/requerant/" as const;

const RouteurPublic = createRouter({
  routeTree,
  defaultPreload: "intent",
  scrollRestoration: true,
});

export { RouteurPublic };
