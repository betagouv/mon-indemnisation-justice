import { DossierApercu } from "@/apps/requerant/models/Dossier.ts";
import { EtatDossierType } from "@/apps/requerant/models/EtatDossier.ts";
import { DossierManagerInterface } from "@/apps/requerant/services/DossierManager.ts";
import { Loader } from "@/common/composants/Loader.tsx";
import { dateSimple } from "@/common/services/date.ts";
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import Badge from "@codegouvfr/react-dsfr/Badge";
import { Button } from "@codegouvfr/react-dsfr/Button";
import type { AlertProps } from "@codegouvfr/react-dsfr/src/Alert.tsx";
import Table from "@codegouvfr/react-dsfr/Table";
import Tooltip from "@codegouvfr/react-dsfr/Tooltip";
import { useQuery } from "@tanstack/react-query";
import { createFileRoute } from "@tanstack/react-router";
import { useInjection } from "inversify-react";
import React from "react";

export const Route = createFileRoute("/requerant/mes-demandes")({
  component: RouteComponent,
});

const severiteBadgeEtat = (
  etat?: EtatDossierType,
): AlertProps.Severity | undefined => {
  if (etat?.estAccepte) {
    return "success";
  }

  if (etat?.type == "KO_REJETE") {
    return "error";
  }
};

const severiteBadgeExtraClass = (etat?: EtatDossierType): string => {
  switch (etat?.type) {
    case "CLOTURE":
      return "fr-badge--beige-gris-galet";
    case "EN_INSTRUCTION":
      return "fr-badge--blue-ecume";
    case "DEPOSE":
      return "fr-badge--blue-cumulus";
  }

  return "";
};

function RouteComponent() {
  const dossierManager = useInjection<DossierManagerInterface>(
    DossierManagerInterface.$,
  );

  const {
    data: dossiers,
    isPending,
    error,
  } = useQuery<DossierApercu[]>({
    queryKey: ["dossier-bris-de-porte-apercu"],
    queryFn: async () => {
      return await dossierManager.mesDemandes();
    },
    retry: false,
    retryDelay: 0,
    throwOnError: false,
  });

  if (isPending || !dossiers) {
    return (
      <>
        <Loader />
      </>
    );
  }

  if (error) {
    return (
      <>
        <Alert
          severity="error"
          title={"Erreur lors de la récupération des dossiers"}
          description={<p>{error?.message ?? "Raison inconnue"}</p>}
        ></Alert>
      </>
    );
  }

  return (
    <div>
      <div className="fr-grid-row">
        <h1>Mes demandes</h1>
      </div>

      <div className="fr-grid-row">
        <div className="fr-col-12"></div>
        <Table
          caption={"Mes demandes"}
          fixed={true}
          headers={[
            "Référence",
            "Statut de la demande d'indemnisation",
            "Date du dernier statut",
            "",
          ]}
          data={dossiers.map((dossier) => [
            dossier.estDepose ? dossier.reference : "",
            <Badge severity={severiteBadgeEtat(dossier.etatActuel.etat)}>
              {dossier.estCloture ? (
                <Tooltip
                  title={dossier.etatActuel.contexte?.explication}
                  kind="hover"
                >
                  {dossier.etatActuel.etat.libelle}
                </Tooltip>
              ) : (
                <>{dossier.etatActuel.etat.libelle}</>
              )}
            </Badge>,
            dateSimple(dossier.etatActuel.date),
            <>
              {!dossier.estCloture &&
                dossier.estEditable &&
                !dossier.estDepose && (
                  <Button
                    title={`Modifier ma demande d'indemnisation ${dossier.reference}`}
                    iconId={"fr-icon-ball-pen-line"}
                    size={"small"}
                    linkProps={{
                      to: `/requerant/dossier/bris-de-porte/$reference`,
                      params: {
                        reference: dossier.reference,
                      },
                    }}
                  >
                    Éditer
                  </Button>
                )}
            </>,
          ])}
        />
      </div>
    </div>
  );
}
