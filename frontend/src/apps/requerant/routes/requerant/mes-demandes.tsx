import { DossierApercu } from "@/apps/requerant/models/Dossier.ts";
import { EtatDossierType } from "@/apps/requerant/models/EtatDossier.ts";
import { RouteurRequerant } from "@/apps/requerant/routeur";
import { DossierManagerInterface } from "@/apps/requerant/services/DossierManager.ts";
import {
  ModaleDsfr,
  useModaleDsfr,
} from "@/common/composants/dsfr/useModaleDsfr.ts";
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
import React, { useMemo } from "react";

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

type Flash = {
  type: "success" | "error" | "warning" | "info";
  titre: string;
  message: string | string[];
};

/**
 * Crée le composant de modale d'affichage d'un message flash, lié à la modale
 * DSFR `modale`.
 *
 * Chaque appel retourne un nouveau composant : l'appel doit donc être mémoïsé,
 * sans quoi React le démonterait (et perdrait son état) à chaque rendu.
 */
const creerModaleMessageFlash = (modale: ModaleDsfr) =>
  function ModaleMessageFlash({ flash }: { flash: Flash }) {
    return (
      <modale.Component title="" size="medium">
        <div className="fr-grid-row" style={{ gap: "1rem" }}>
          <Alert
            severity={flash.type}
            title={flash.titre || ""}
            description={
              Array.isArray(flash.message) ? (
                <>
                  {flash.message.map((message, index) => (
                    <p key={index}>{message}</p>
                  ))}
                </>
              ) : (
                flash.message || ""
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
                onClick: () => modale.close(),
              },
            ]}
          />
        </div>
      </modale.Component>
    );
  };

/**
 * Fournit une modale d'affichage d'un message flash, propre au composant
 * appelant, ainsi que les fonctions pour l'ouvrir et la fermer.
 *
 * La modale s'ouvre d'elle-même dès qu'elle est montée.
 */
const useModaleMessageFlash = () => {
  const modale = useModaleDsfr("modale-message-flash", {
    ouverteParDefaut: true,
  });

  const ModaleMessageFlash = useMemo(
    () => creerModaleMessageFlash(modale),
    [modale],
  );

  return {
    modale: ModaleMessageFlash,
    ouvrir: modale.open,
    fermer: modale.close,
  };
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
    throwOnError: true,
  });

  const { modale: ModaleMessageFlash } = useModaleMessageFlash();

  const state = useRouterState<typeof RouteurRequerant, { flash?: Flash }>({
    select: (s) => s.location.state as { flash?: Flash },
    router: RouteurRequerant,
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
    <>
      {state.flash && <ModaleMessageFlash flash={state.flash} />}

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
                    style={{
                      textTransform: "initial",
                      fontWeight: "initial",
                    }}
                    title={
                      dossier.etatActuel.contexte?.explication ??
                      "Dossier clôturé"
                    }
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
                {dossier.estDecide && (
                  <Button
                    title="Consulter la décision"
                    iconId={"fr-icon-eye-line"}
                    size={"small"}
                    linkProps={{
                      to: `/requerant/dossier/bris-de-porte/$id`,
                      params: {
                        id: dossier.id,
                      },
                    }}
                  >
                    Consulter la décision
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
