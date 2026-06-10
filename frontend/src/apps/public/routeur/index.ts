import { createRouter, useNavigate } from "@tanstack/react-router";
import { routeTree } from "./routeur-public.gen";

export const REQUERANT_URL = "/requerant/" as const;

const RouteurPublic = createRouter({
  routeTree,
  defaultPreload: "intent",
  scrollRestoration: true,
});

// Paths valides de l'app public — sert à typer usePublicNavigate
export type PublicPath =
  | "/dysfonctionnement/tester-mon-eligibilite/"
  | "/dysfonctionnement/tester-mon-eligibilite/test-eligibilite"
  | "/dysfonctionnement/tester-mon-eligibilite/1-date-decision"
  | "/dysfonctionnement/tester-mon-eligibilite/2-action-contentieuse"
  | "/dysfonctionnement/tester-mon-eligibilite/3-type-decision"
  | "/dysfonctionnement/tester-mon-eligibilite/4-pieces-procedure"
  | "/dysfonctionnement/tester-mon-eligibilite/5-diligences"
  | "/dysfonctionnement/tester-mon-eligibilite/resultat";

// Le routeur public n'est pas enregistré dans le Register global de TanStack Router
// (le routeur requerant y est déjà). Sans enregistrement, les search params sont inférés
// comme `unknown` et deviennent obligatoires sur chaque navigate. Ce hook encapsule le
// cast une seule fois plutôt que de le répéter dans chaque fichier de route.
export const usePublicNavigate = () =>
  useNavigate() as (opts: {
    to: PublicPath;
    state?: Record<string, unknown>;
  }) => void;


export { RouteurPublic };
