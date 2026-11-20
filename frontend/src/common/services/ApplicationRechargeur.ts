/**
 * Lorsqu'un module ne peut être chargé, il s'agit très probablement d'un _chunk_
 * vite qui n'existe plus suite à un déploiement ayant impacté le module, comme
 * la modification d'un composant sur une page du routeur Tanstack.
 *
 * Ce service a pour vocation de détecter ce type d'erreur et de forcer le rechargement
 * de la page.
 */
export class ApplicationRechargeur {
  // Messages levés par les navigateurs lorsqu'un `import()` dynamique échoue
  private static readonly ERREUR_CHARGEMENT_MODULE: RegExp[] = [
    // Chrome / Edge
    /Failed to fetch dynamically imported module/i,
    // Firefox
    /error loading dynamically imported module/i,
    // Safari
    /Importing a module script failed/i,
    // Préchargement CSS / JS de Vite
    /Unable to preload CSS/i,
    /Failed to load module script/i,
  ];

  private constructor(
    private readonly cleSession: string,
    private readonly delaiMinimalEntreRechargementsMs: number,
  ) {}

  private static readonly instance = new ApplicationRechargeur(
    "mij:rechargement-module",
    // Délai entre 2 rechargements ppur éviter des boucles infinies
    30_000,
  );

  /**
   * Indique si l'erreur est une erreur de chargement de module.
   */
  static estErreurChargementModule(erreur: unknown): boolean {
    if (!erreur) {
      return false;
    }

    // Extraction du message d'erreur
    const message =
      erreur instanceof Error
        ? `${erreur.name}: ${erreur.message}`
        : String((erreur as { message?: unknown })?.message ?? erreur);

    // Si le message d'erreur correspond à une expression régulière des erreurs connues
    return ApplicationRechargeur.ERREUR_CHARGEMENT_MODULE.some((motif) =>
      motif.test(message),
    );
  }

  /**
   * Recharge la page si aucun rechargement n'a eu lieu récemment.
   *
   * @return `true` si le rechargement a été déclenché, `false` s'il a été bloqué par le garde-fou
   */
  static rechargerSiPossible(): boolean {
    return ApplicationRechargeur.instance.rechargerSiPossible();
  }

  /**
   * Recharge la page si l'erreur est une erreur de chargement de module.
   *
   * @return `true` si le rechargement a été déclenché
   */
  static rechargerSiErreurChargementModule(erreur: unknown): boolean {
    return ApplicationRechargeur.instance.rechargerSiErreurChargementModule(
      erreur,
    );
  }

  private rechargerSiErreurChargementModule(erreur: unknown): boolean {
    return (
      ApplicationRechargeur.estErreurChargementModule(erreur) &&
      this.rechargerSiPossible()
    );
  }

  private rechargerSiPossible(): boolean {
    const maintenant = Date.now();

    if (
      maintenant - this.lireDernierRechargement() <
      this.delaiMinimalEntreRechargementsMs
    ) {
      return false;
    }

    try {
      sessionStorage.setItem(this.cleSession, maintenant.toString());
    } catch {
      // Sans `sessionStorage`, on ne peut garantir l'absence de boucle : on s'abstient de recharger
      return false;
    }

    window.location.reload();
    return true;
  }

  private lireDernierRechargement(): number {
    try {
      return parseInt(sessionStorage.getItem(this.cleSession) ?? "0") || 0;
    } catch {
      return 0;
    }
  }
}
