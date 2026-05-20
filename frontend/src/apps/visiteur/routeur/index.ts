import { createRouter, useNavigate } from "@tanstack/react-router";
import { routeTree } from "./routeur-visiteur.gen";
import type { ReponsesEligibilite } from "@/apps/visiteur/components/types";

export const REQUERANT_URL = "/requerant/" as const;

const RouteurVisiteur = createRouter({
  routeTree,
  defaultPreload: "intent",
  scrollRestoration: true,
});

// Paths valides de l'app visiteur — sert à typer useVisiteurNavigate
export type VisiteurPath =
  | "/deni-de-justice/tester-mon-eligibilite/"
  | "/deni-de-justice/tester-mon-eligibilite/test-eligibilite"
  | "/deni-de-justice/tester-mon-eligibilite/etapes-eligibilite"
  | "/deni-de-justice/tester-mon-eligibilite/resultat";

// Le routeur visiteur n'est pas enregistré dans le Register global de TanStack Router
// (le routeur requerant y est déjà). Sans enregistrement, les search params sont inférés
// comme `unknown` et deviennent obligatoires sur chaque navigate. Ce hook encapsule le
// cast une seule fois plutôt que de le répéter dans chaque fichier de route.
export const useVisiteurNavigate = () =>
  useNavigate() as (opts: {
    to: VisiteurPath;
    state?: Record<string, unknown>;
  }) => void;

declare module "@tanstack/react-router" {
  interface HistoryState {
    reponses?: ReponsesEligibilite;
  }
}

export { RouteurVisiteur };
