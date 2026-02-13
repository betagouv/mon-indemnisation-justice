import Download from "@codegouvfr/react-dsfr/Download";
import React from "react";
import { createFileRoute } from "@tanstack/react-router";
import Tabs from "@codegouvfr/react-dsfr/Tabs";
import { router as RouteurFDO } from "@/apps/agent/fdo/_init";
import urlAvisIntervention from "@/apps/agent/fdo/fichiers/documents/avis-d-intervention-en-cas-de-bris-de-porte.pdf";
import urlGuideRemiseAvis from "@/apps/agent/fdo/fichiers/documents/guide-de-remise-de-l-avis-d-intervention-apres-un-bris-de-porte.pdf";
import urlGuideDeclaration from "@/apps/agent/fdo/fichiers/documents/guide-de-declaration-de-bris-de-porte-a-destination-des-Forces-de-l-ordre.pdf";
import urlGuideDeclarationPN from "@/apps/agent/fdo/fichiers/documents/guide-de-declaration-de-bris-de-porte-a-destination-des-Forces-de-l-ordre-pn.pdf";

export const Route = createFileRoute("/agent/fdo/les-documents")({
  component: () => {
    const { agent } = Route.useRouteContext<typeof RouteurFDO>();
    console.log(agent.administration.type);

    return (
      <div>
        <h1 className="fr-my-2w">Les documents</h1>

        <p>
          Vous trouverez ci-dessous les attestations dans leur version la plus
          récente. Ces documents sont <b>uniquement</b> destinés aux agents des
          Forces de l'ordre.
        </p>

        <p>
          Par ailleurs, ces documents étant susceptibles d'être mis à jour
          régulièrement, nous vous invitons à les imprimer qu'en quantité
          limitée et revenir sur cette page pour récupérer la dernière version
          du document requis.
        </p>

        <Tabs
          tabs={[
            {
              content: (
                <>
                  <Download
                    label="Avis d’intervention à remettre à l’usager"
                    details={""}
                    linkProps={{
                      to: `${window.location.origin}${urlAvisIntervention}`,
                      replace: true,
                      download: true,
                    }}
                  />

                  <div className="fr-grid-row fr-col-12">
                    <object
                      data={urlAvisIntervention}
                      type="application/pdf"
                      style={{ width: "100%", aspectRatio: "210/297" }}
                    ></object>
                  </div>
                </>
              ),
              label: "Avis d'intervention",
              isDefault: true,
            },
            {
              content: (
                <>
                  <Download
                    label="Guide de remise de l’avis d’intervention après un bris de porte"
                    details={""}
                    linkProps={{
                      to: `${window.location.origin}${urlGuideRemiseAvis}`,
                      replace: true,
                      download: true,
                    }}
                  />

                  <div className="fr-grid-row fr-col-12">
                    <object
                      data={urlGuideRemiseAvis}
                      type="application/pdf"
                      style={{ width: "100%", aspectRatio: "210/297" }}
                    ></object>
                  </div>
                </>
              ),
              label: "Guide de remise de l'avis d'intervention",
              isDefault: false,
            },
            {
              content: (
                <>
                  <Download
                    label="Guide de déclaration en ligne d'un bris de porte"
                    details={""}
                    linkProps={{
                      to: `${window.location.origin}${agent.administration.type === "PN" ? urlGuideDeclarationPN : urlGuideDeclaration}`,
                      replace: true,
                      download: true,
                    }}
                  />

                  <div className="fr-grid-row fr-col-12">
                    <object
                      data={
                        agent.administration.type === "PN"
                          ? urlGuideDeclarationPN
                          : urlGuideDeclaration
                      }
                      type="application/pdf"
                      style={{ width: "100%", aspectRatio: "210/297" }}
                    ></object>
                  </div>
                </>
              ),
              label: "Guide de déclaration en ligne",
              isDefault: false,
            },
          ]}
        />
      </div>
    );
  },
});
