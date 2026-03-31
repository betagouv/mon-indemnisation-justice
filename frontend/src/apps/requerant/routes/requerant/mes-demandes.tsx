import { DossierApercu } from "@/apps/requerant/models/Dossier.ts";
import { EtatDossierType } from "@/apps/requerant/models/EtatDossier.ts";
import { RouteurRequerant } from "@/apps/requerant/routeur";
import { DossierManagerInterface } from "@/apps/requerant/services/DossierManager.ts";
import { Modale, ModaleRef } from "@/common/composants/dsfr/Modale.tsx";
import { Loader } from "@/common/composants/Loader.tsx";
import { dateEtHeureSimple } from "@/common/services/date.ts";
import { Alert } from "@codegouvfr/react-dsfr/Alert";
import Badge from "@codegouvfr/react-dsfr/Badge";
import { Button } from "@codegouvfr/react-dsfr/Button";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import type { AlertProps } from "@codegouvfr/react-dsfr/src/Alert.tsx";
import Table from "@codegouvfr/react-dsfr/Table";
import Tooltip from "@codegouvfr/react-dsfr/Tooltip";
import { useQuery } from "@tanstack/react-query";
import { createFileRoute, useRouterState } from "@tanstack/react-router";
import { useInjection } from "inversify-react";
import React, { useEffect, useRef } from "react";

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

  const modaleRef = useRef<ModaleRef>(null);

  const state = useRouterState<
    typeof RouteurRequerant,
    {
      flash?: {
        type: "success" | "error" | "warning" | "info";
        titre: string;
        message: string;
      };
    }
  >({
    select: (s) =>
      s.location.state as {
        flash?: {
          type: "success" | "error" | "warning" | "info";
          titre: string;
          message: string;
        };
      },
    router: RouteurRequerant,
  });

  useEffect(() => {
    if (!!state.flash && modaleRef.current) {
      // On délaye de 250ms histoire d'être certain que la modale soit rendered
      setTimeout(() => modaleRef.current?.ouvrir(), 250);
    }
  }, []);

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
    <>
      <Modale id="dossier-depose-modale" title="" size="medium" ref={modaleRef}>
        {state.flash && (
          <div className="fr-grid-row" style={{ gap: "1rem" }}>
            <Alert
              severity={state.flash.type}
              title={state.flash.titre || ""}
              description={
                Array.isArray(state.flash.message) ? (
                  <>
                    {state.flash.message.map((message, index) => (
                      <p key={index}>{message}</p>
                    ))}
                  </>
                ) : (
                  state.flash.message || ""
                )
              }
            />
            <ButtonsGroup
              className="fr-col-12 fr-mx-0"
              inlineLayoutWhen="always"
              alignment="right"
              buttonsEquisized={false}
              buttonsSize="small"
              buttons={[
                {
                  className: "fr-mx-0",
                  children: "Fermer",
                  priority: "primary",
                  onClick: () => modaleRef.current?.fermer(),
                },
              ]}
            />
          </div>
        )}
      </Modale>

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
              dossier.estDepose ? (
                <b>
                  <pre>{dossier.reference}</pre>
                </b>
              ) : (
                ""
              ),
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
              <>le {dateEtHeureSimple(dossier.etatActuel.date)}</>,
              <div className="fr-grid-row fr-grid-row--right ">
                {dossier.estEditable && (
                  <Button
                    title={
                      dossier.estBrouillon
                        ? "Compléter ma demande"
                        : "Modifier ma demande"
                    }
                    iconId={"fr-icon-ball-pen-line"}
                    size={"small"}
                    linkProps={{
                      to: `/requerant/dossier/bris-de-porte/$id`,
                      params: {
                        id: dossier.id,
                      },
                    }}
                  >
                    {dossier.estBrouillon
                      ? "Compléter ma demande"
                      : "Modifier ma demande"}
                  </Button>
                )}
              </div>,
            ])}
          />
        </div>
      </div>
    </>
  );
}
