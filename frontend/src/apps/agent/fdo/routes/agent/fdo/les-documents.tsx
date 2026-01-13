import Download from "@codegouvfr/react-dsfr/Download";
import React from "react";
import { createFileRoute } from "@tanstack/react-router";
import Tabs from "@codegouvfr/react-dsfr/Tabs";
import urlAttestation from "@/assets/attestations/v1/attestation-a-remettre-en-cas-d-erreur-de-porte.pdf";
import urlGuideREmise from "@/assets/attestations/v1/guide-de-remise-de-l-attestation.pdf";

export const Route = createFileRoute("/agent/fdo/les-documents")({
  component: () => (
    <div>
      <h1 className="fr-my-2w">Les documents</h1>

      <p>
        Vous trouverez ci-dessous les attestations dans leur version la plus
        récente. Ces documents sont <b>uniquement</b> destinés aux agents des
        Forces de l'ordre.
      </p>

      <p>
        Par ailleurs, ces documents étant susceptibles d'être mis à jour
        régulièrement, nous vous invitons à les imprimer qu'en quantité limitée
        et revenir sur cette page pour récupérer la dernière version du document
        requis.
      </p>

      <Tabs
        tabs={[
          {
            content: (
              <>
                <Download
                  label="Attestation à remettre en cas d'erreur de porte"
                  details={""}
                  linkProps={{
                    href: urlAttestation,
                    download: true,
                  }}
                />

                <div className="fr-grid-row fr-col-12">
                  <object
                    data={urlAttestation}
                    type="application/pdf"
                    style={{ width: "100%", aspectRatio: "210/297" }}
                  ></object>
                </div>
              </>
            ),
            label: "Attestation à remettre",
            isDefault: true,
          },
          {
            content: (
              <>
                <Download
                  label="Guide de remise de l'attestation"
                  details={""}
                  linkProps={{
                    href: urlGuideREmise,
                    download: true,
                  }}
                />

                <div className="fr-grid-row fr-col-12">
                  <object
                    data={urlGuideREmise}
                    type="application/pdf"
                    style={{ width: "100%", aspectRatio: "210/297" }}
                  ></object>
                </div>
              </>
            ),
            label: "Guide de remise",
            isDefault: true,
          },
        ]}
      />
    </div>
  ),
});
