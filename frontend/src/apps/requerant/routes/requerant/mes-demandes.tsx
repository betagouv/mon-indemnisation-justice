import React from "react";
import { createFileRoute } from "@tanstack/react-router";
import Table from "@codegouvfr/react-dsfr/Table";
import { dateIlYaNJours, dateSimple } from "@/common/services/date.ts";
import Badge from "@codegouvfr/react-dsfr/Badge";
import Tooltip from "@codegouvfr/react-dsfr/Tooltip";
import { Button } from "@codegouvfr/react-dsfr/Button";

export const Route = createFileRoute("/requerant/mes-demandes")({
  component: RouteComponent,
});

type DossierApercu = {
  id: string;
  reference: string;
  dateDernierStatut: Date;
  estSigne: boolean;
  estAccepte?: boolean;
  estCloture: boolean;
  justificationCloture?: string;
  estEditable: boolean;
  estDepose: boolean;
};

const dossiers: DossierApercu[] = [
  {
    id: "324",
    reference: "BRI/20260101/001",
    dateDernierStatut: dateIlYaNJours(3),
    estSigne: false,
    estCloture: false,
    estEditable: true,
    estDepose: false,
  },
];

function RouteComponent() {
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
            dossier.reference,
            <>
              {dossier.estSigne &&
                (dossier.estAccepte ? (
                  <Badge severity={"success"}>Indemnisation à accepter</Badge>
                ) : (
                  <Badge severity={"error"}>Dossier rejeté</Badge>
                ))}
              {dossier.estCloture && (
                <Badge className={"fr-badge--beige-gris-galet"}>
                  <Tooltip title={dossier.justificationCloture} kind="hover">
                    Dossier clôturé
                  </Tooltip>
                </Badge>
              )}
              {!dossier.estEditable && (
                <Badge className={"fr-badge--blue-ecume"}>
                  Dossier en cours d'instruction
                </Badge>
              )}
              {dossier.estDepose && (
                <Badge className={"fr-badge--blue-cumulus"} noIcon={true}>
                  Dossier déposé
                </Badge>
              )}
              {!dossier.estEditable && (
                <Badge className={"fr-badge--blue-ecume"} noIcon={true}>
                  Dossier en cours de constitution
                  <span className="fr-icon-time-line" aria-hidden="true"></span>
                </Badge>
              )}
            </>,
            dateSimple(dossier.dateDernierStatut),
            <>
              {!dossier.estCloture &&
                dossier.estEditable &&
                !dossier.estDepose && (
                  <Button
                    title={`Modifier ma demande d'indemnisation ${dossier.reference}`}
                    iconId={"fr-icon-ball-pen-line"}
                    size={"small"}
                    linkProps={{
                      to: `/requerant/demande/bris-de-porte/${dossier.id}`,
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
