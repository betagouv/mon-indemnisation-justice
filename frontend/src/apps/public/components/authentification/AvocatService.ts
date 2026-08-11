import { AvocatTrouve } from "./authentification.schemas";

export async function rechercherAvocats(recherche: string): Promise<AvocatTrouve[]> {
  const reponse = await fetch(`/api/public/dysfonctionnement/avocats?${new URLSearchParams({ r: recherche })}`);
  const donnees: { resultats: AvocatTrouve[] } = await reponse.json();

  return donnees.resultats;
}
