import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { dateSimple } from "@common/services/date.ts";
import { plainToInstance } from "class-transformer";
import React, { useEffect, useState } from "react";
import "./liste/dossier-liste-element.css";
import { DossierAAttribuer } from "./liste/DossierAAttribuer.ts";

function DossierAAttribuerLigne({ dossier }: { dossier: DossierAAttribuer }) {
  return (
    <div className="fr-grid-row mij-dossier-liste-element">
      <div className="fr-col-3">
        <strong className="fr-text--bold fr-text--lg">
          {dossier.reference}
        </strong>
      </div>

      <div className="fr-col-7 mij-dossier-details">
        <ul>
          <li>{dossier.requerant}</li>
          <li>
            {dossier.adresse ?? (
              <>
                adresse <i>non renseignée</i>
              </>
            )}
          </li>
          <li>
            survenu{" "}
            {dossier.dateOperation ? (
              <>le {dateSimple(dossier.dateOperation)}</>
            ) : (
              <>
                à une date <i>non renseignée</i>
              </>
            )}
          </li>
        </ul>
      </div>

      <div className="fr-col-2">
        <ButtonsGroup
          inlineLayoutWhen="always"
          alignment="right"
          buttonsIconPosition="left"
          buttonsEquisized={false}
          buttonsSize="small"
          buttons={[
            {
              size: "small",
              priority: "tertiary no outline",
              iconId: "fr-icon-eye-line",
              children: "Consulter",
              className: "fr-mb-0",
              linkProps: {
                to: "/dossier/$id",
                params: {
                  id: dossier.id,
                },
              },
            },
          ]}
        />
      </div>
    </div>
  );
}

export function ListeDossierAAttribuer() {
  const [dossiers, setDossiers]: [
    DossierAAttribuer[],
    (dossiers: DossierAAttribuer[]) => void,
  ] = useState<DossierAAttribuer[]>([]);

  // TODO utiliser une tanstack query ici (notamment en vue de la mutation)
  useEffect(() => {
    fetch("/api/agent/fip6/dossiers/liste/a-attribuer")
      .then((response) => response.json())
      .then((data) =>
        setDossiers(plainToInstance(DossierAAttribuer, data as any[])),
      );
  }, []);

  return (
    <>
      <h1>Dossiers à attribuer</h1>

      <p>
        Les dossiers ci-dessous ont été récemment déposés et attendent d'être
        attribués à un rédacteur.
      </p>

      <h4>
        {dossiers.length ? (
          <>
            {dossiers.length} dossier{dossiers.length > 1 ? "s" : ""}
          </>
        ) : (
          <>Aucun dossier</>
        )}
      </h4>

      <div>
        {dossiers.map((dossier: DossierAAttribuer) => (
          <DossierAAttribuerLigne
            key={`dossier-a-attribuer-${dossier.id}`}
            dossier={dossier}
          />
        ))}
      </div>
    </>
  );
}
