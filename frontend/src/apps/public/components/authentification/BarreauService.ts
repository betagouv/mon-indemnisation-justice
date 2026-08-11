import { queryClient } from "@/apps/public/query";
import { Barreau } from "./authentification.schemas";

export async function listerBarreaux(): Promise<Barreau[]> {
  return queryClient.fetchQuery({
    queryKey: ["dysfonctionnement", "barreaux"],
    queryFn: async (): Promise<Barreau[]> => {
      const reponse = await fetch("/api/public/dysfonctionnement/barreaux");

      return await reponse.json();
    },
    staleTime: 60 * 60 * 1000, // 1 heure
  });
}
