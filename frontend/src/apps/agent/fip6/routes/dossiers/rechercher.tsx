import { AgentContext } from "@/apps/agent/_commun/contexts";
import {
  RechercherDossierPage,
  RechercheRequete,
  requeteVersParametres,
  validerParametres,
} from "@/apps/agent/fip6/composants/pages/RechercherDossierPage.tsx";
import { container } from "@/apps/agent/fip6/container";
import { Agent, EtatDossierType, Redacteur } from "@/common/models";
import { AgentManagerInterface } from "@/common/services/agent/agent.ts";
import "@/style/agents.css";
import { RegisteredLinkProps } from "@codegouvfr/react-dsfr/src/link.tsx";
import { createFileRoute } from "@tanstack/react-router";
import React, { useMemo } from "react";

export const Route = createFileRoute("/dossiers/rechercher")({
  loader: async ({ context }: { context: AgentContext }) => ({
    agent: context.agent,
    // TODO transformer par un appel API
    redacteurs: await container.get(AgentManagerInterface.$).redacteurs(),
  }),
  validateSearch: validerParametres,
  component: RechercherComposant,
});

function RechercherComposant() {
  // Récupérer l'agent actuellement connecté
  const { agent, redacteurs }: { agent: Agent; redacteurs: Redacteur[] } =
    Route.useLoaderData();

  // Récupérer les paramètres de recherche
  const {
    p,
    a,
    e,
    r,
  }: {
    p: number;
    a?: string;
    e?: string;
    r?: string;
  } = Route.useSearch();

  // L'état de la requête de recherche de dossiers, calculée à partir des paramères GET
  const requete: RechercheRequete = useMemo<RechercheRequete>(() => {
    const etats = e?.split("|");
    const attributaires = a?.split("|").map((id) => parseInt(id));

    return {
      etatsDossier: etats
        ? EtatDossierType.liste.filter((e) => etats.includes(e.slug))
        : [
            EtatDossierType.A_ATTRIBUER,
            EtatDossierType.A_INSTRUIRE,
            EtatDossierType.EN_INSTRUCTION,
            EtatDossierType.OK_A_SIGNER,
            EtatDossierType.OK_A_APPROUVER,
            EtatDossierType.OK_A_VERIFIER,
            EtatDossierType.OK_VERIFIE,
            EtatDossierType.OK_A_INDEMNISER,
            EtatDossierType.OK_EN_ATTENTE_PAIEMENT,
            EtatDossierType.OK_INDEMNISE,
            EtatDossierType.KO_A_SIGNER,
            EtatDossierType.KO_REJETE,
          ],
      attributaires: attributaires
        ? redacteurs.filter((redacteur: Redacteur) =>
            attributaires.includes(redacteur.id),
          )
        : [],
      inclureDossierNonAttribue: !!a?.split("|").find((v) => v === "_"),
      recherche: r ?? "",
      page: p,
    };
  }, [p, e, a, r]);

  return (
    <RechercherDossierPage
      agent={agent}
      redacteurs={redacteurs}
      requete={requete}
      construireLien={({ changement }): RegisteredLinkProps => {
        return {
          to: Route.fullPath,
          search: requeteVersParametres({ ...requete, ...changement }),
        } as RegisteredLinkProps;
      }}
    />
  );
}
