import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { periode } from "@common/services/date.ts";
import { plainToInstance } from "class-transformer";
import React, { useEffect, useState } from "react";
import "./liste/dossier-liste-element.css";
import { DossierDeclarationAcceptationAVerifier } from "./liste/DossierDeclarationAcceptationAVerifier";

function DossierDeclarationAcceptationAVerifierLigne({
  dossier,
}: {
  dossier: DossierDeclarationAcceptationAVerifier;
}) {
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
          <li>{dossier.montantIndemnisation}</li>
          <li>accepté il y a {periode(dossier.dateAcceptation)}</li>
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

export function ListeDossierDeclarationAcceptationAVerifier() {
  const [dossiers, setDossiers]: [
    DossierDeclarationAcceptationAVerifier[],
    (dossiers: DossierDeclarationAcceptationAVerifier[]) => void,
  ] = useState<DossierDeclarationAcceptationAVerifier[]>([]);

  // TODO utiliser une tanstack query ici (notamment en vue de la mutation)
  useEffect(() => {
    fetch("/api/agent/fip6/dossiers/liste/a-verifier")
      .then((response) => response.json())
      .then((data) =>
        setDossiers(
          plainToInstance(
            DossierDeclarationAcceptationAVerifier,
            data as any[],
          ),
        ),
      );
  }, []);

  return (
    <>
      <h1>Dossiers en attente d'arrêté de paiement</h1>

      <p>
        Vos dossiers attribués, ci-dessous, ont reçu une déclaration
        d'acceptation à vérifier et attendent un arrêté de paiement que vous
        pouvez désormais initier.
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
        {dossiers.map((dossier: DossierDeclarationAcceptationAVerifier) => (
          <DossierDeclarationAcceptationAVerifierLigne
            key={`dossier-a-attribuer-${dossier.id}`}
            dossier={dossier}
          />
        ))}
      </div>
    </>
  );
}
