import { useEffect, useRef } from "react";

/**
 * URL du hub Mercure, servi sur la même origine que l'application (FrankenPHP).
 */
export const URL_HUB_MERCURE = "/.well-known/mercure";

/**
 * S'abonne au `topic` Mercure et appelle `onMessage` avec les données (JSON) de chaque mise à jour reçue.
 *
 * L'autorisation des mises à jour privées repose sur le cookie `mercureAuthorization` posé par le backend, d'où
 * `withCredentials`. Passer `null` comme topic désactive l'abonnement.
 */
export function useAbonnementMercure<T = unknown>(
  topic: string | null,
  onMessage: (donnees: T) => void,
): void {
  // Conserver la dernière version du callback sans provoquer de réabonnement
  const onMessageRef = useRef(onMessage);
  onMessageRef.current = onMessage;

  useEffect(() => {
    if (!topic) {
      return;
    }

    const url = new URL(URL_HUB_MERCURE, window.location.origin);
    url.searchParams.append("topic", topic);

    const source = new EventSource(url, { withCredentials: true });

    source.onmessage = (evenement: MessageEvent<string>) => {
      try {
        onMessageRef.current(JSON.parse(evenement.data) as T);
      } catch (erreur) {
        console.error("Message Mercure invalide", erreur);
      }
    };

    return () => source.close();
  }, [topic]);
}
