// Lit le bloc JSON injecté par Symfony dans le HTML initial de la SPA (cf. PublicController::index et
// templates/public/public.html.twig), pour obtenir les jetons CSRF sans aller-retour réseau dédié.
type ReactArguments = {
  jetonsCsrf: Record<string, string>;
};

function lireArguments(): ReactArguments {
  try {
    return JSON.parse(document.getElementById("react-arguments")?.textContent ?? "{}");
  } catch {
    return { jetonsCsrf: {} };
  }
}

export const reactArguments: ReactArguments = lireArguments();

export function obtenirJetonCsrf(intention: string = "authenticate"): string {
  return reactArguments.jetonsCsrf[intention] ?? "";
}
