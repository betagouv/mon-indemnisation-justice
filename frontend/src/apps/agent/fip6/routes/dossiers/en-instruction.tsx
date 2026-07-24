import { AgentContext } from "@/apps/agent/_commun/contexts/AgentContext.ts";
import { DossierEnInstruction } from "@fip6/modeles";
import { dateSimple, periode } from "@common/services/date.ts";
import ButtonsGroup from "@codegouvfr/react-dsfr/ButtonsGroup";
import { createFileRoute } from "@tanstack/react-router";
import { plainToInstance } from "class-transformer";
import React, { useEffect, useState } from "react";

export const Route = createFileRoute("/dossiers/en-instruction")({
  beforeLoad: async ({ context }: { context: AgentContext }) => {
    // TDOO: s'assurer que l'agent est rédacteur
  },
  component: ListeDossierEnInstruction,
});

function ListeDossierEnInstruction() {
  const [dossiers, setDossiers]: [
    DossierEnInstruction[],
    (dossiers: DossierEnInstruction[]) => void,
  ] = useState<DossierEnInstruction[]>([]);

  // TODO utiliser une tanstack query ici (notamment en vue de la mutation)
  useEffect(() => {
    fetch("/api/agent/fip6/dossiers/liste/en-instruction")
      .then((response) => response.json())
      .then((data) =>
        setDossiers(plainToInstance(DossierEnInstruction, data as any[])),
      );
  }, []);

  return (
    <>
      <h1>Dossiers en cours d'instruction</h1>

      <p>Vous avez démarrer l'instruction des dossiers ci-dessous.</p>

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
        {dossiers.map((dossier: DossierEnInstruction) => (
          <DossierEnInstructionLigne
            key={`dossier-a-attribuer-${dossier.id}`}
            dossier={dossier}
          />
        ))}
      </div>
    </>
  );
}

function DossierEnInstructionLigne({
  dossier,
}: {
  dossier: DossierEnInstruction;
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
          <li>
            {dossier.adresse ? (
              dossier.adresse
            ) : (
              <>
                adresse <i>non renseignée</i>
              </>
            )}
          </li>
          <li>
            intervention{" "}
            {dossier.dateOperation ? (
              <>le {dateSimple(dossier.dateOperation)}</>
            ) : (
              <>
                à une date <i>non renseignée</i>
              </>
            )}
          </li>
          <li>publié il y a {periode(dossier.datePublication)}</li>
          <li>
            instruction démarrée il y a {periode(dossier.dateDebutInstruction)}
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
