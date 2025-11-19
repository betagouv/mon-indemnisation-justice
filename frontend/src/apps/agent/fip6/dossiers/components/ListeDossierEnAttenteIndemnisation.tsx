import React, { useEffect, useMemo, useState } from "react";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import "./liste/dossier-liste-element.css";
import { plainToInstance } from "class-transformer";
import { DossierEnAttenteIndemnisation } from "@/apps/agent/fip6/dossiers/components/liste/DossierEnAttenteIndemnisation.ts";
import { convertirEnEuros } from "@/common/services/devise.ts";
import { dateSimple } from "@/common/services/date.ts";

function DossierEnAttenteIndemnisationLigne({
  dossier,
}: {
  dossier: DossierEnAttenteIndemnisation;
}) {
  const consulterDossierURL = useMemo<string>(
    () => `/agent/redacteur/dossier/${dossier.id}`,
    [dossier.id],
  );

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
          <li>{convertirEnEuros(dossier.montantIndemnisation)}</li>
          <li>transmis le {dateSimple(dossier.dateTransmission)} </li>
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
                href: consulterDossierURL,
              },
            },
          ]}
        />
      </div>
    </div>
  );
}

export function ListeDossierEnAttenteIndemnisation() {
  const [dossiers, setDossiers]: [
    DossierEnAttenteIndemnisation[],
    (dossiers: DossierEnAttenteIndemnisation[]) => void,
  ] = useState<DossierEnAttenteIndemnisation[]>([]);

  useEffect(() => {
    fetch("/api/agent/fip6/dossiers/liste/en-attente-indemnisation")
      .then((response) => response.json())
      .then((data) =>
        setDossiers(
          plainToInstance(DossierEnAttenteIndemnisation, data as any[]),
        ),
      );
  }, []);

  return (
    <>
      <h1>Dossiers en attente de paiement</h1>

      <p>
        Dossiers transmis au Bureau du Budget et pour lesquels le versement de
        l'indemnisation est attendu.
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
        {dossiers.map((dossier: DossierEnAttenteIndemnisation) => (
          <DossierEnAttenteIndemnisationLigne
            key={dossier.id}
            dossier={dossier}
          />
        ))}
      </div>
    </>
  );
}
