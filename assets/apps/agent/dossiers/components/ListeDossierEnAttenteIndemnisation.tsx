import React, { useEffect, useMemo, useState } from "react";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import "./liste/dossier-liste-element.css";
import { plainToInstance } from "class-transformer";
import { DossierEnAttenteIndemnisation } from "@/apps/agent/dossiers/components/liste/DossierEnAttenteIndemnisation.ts";

const formateurMontantEuro = new Intl.NumberFormat("fr-FR", {
  style: "currency",
  currency: "EUR",
  trailingZeroDisplay: "auto",
  minimumFractionDigits: 2,
  maximumFractionDigits: 2,
});

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
          <li>{formateurMontantEuro.format(dossier.montantIndemnisation)}</li>
          <li>
            transmis le{" "}
            {dossier.dateTransmission.toLocaleString("fr-FR", {
              day: "numeric",
              month: "long",
              year:
                dossier.dateTransmission.getFullYear() ===
                new Date().getFullYear()
                  ? undefined
                  : "numeric",
            })}{" "}
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
  ] = useState([]);

  useEffect(() => {
    fetch("/api/agent/dossiers/liste/en-attente-indemnisation")
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
        {dossiers.length} dossier{dossiers.length > 1 ? "s" : ""}
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
