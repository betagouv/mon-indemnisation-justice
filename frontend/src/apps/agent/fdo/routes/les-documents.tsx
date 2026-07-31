import urlAvisIntervention from "@fdo/fichiers/documents/avis-d-intervention-en-cas-de-bris-de-porte.pdf";
import urlAvisInterventionGN from "@fdo/fichiers/documents/avis-d-intervention-en-cas-de-bris-de-porte_gn.pdf";
import urlGuideDeclarationPN from "@fdo/fichiers/documents/guide-de-declaration-de-bris-de-porte-a-destination-des-Forces-de-l-ordre-pn.pdf";
import urlGuideDeclaration from "@fdo/fichiers/documents/guide-de-declaration-de-bris-de-porte-a-destination-des-Forces-de-l-ordre.pdf";
import urlGuideRemiseAvis from "@fdo/fichiers/documents/guide-de-remise-de-l-avis-d-intervention-apres-un-bris-de-porte.pdf";
import { RouteurFDO } from "@fdo/routeur";
import Download from "@codegouvfr/react-dsfr/Download";
import Tabs from "@codegouvfr/react-dsfr/Tabs";
import { AgentFDOContexte } from "@fdo/routeur/contexte.ts";
import { createFileRoute } from "@tanstack/react-router";
import React, { useMemo } from "react";

export const Route = createFileRoute("/les-documents")({
  component: () => {
    const { agent } = Route.useRouteContext<
      typeof RouteurFDO,
      AgentFDOContexte
    >();

    const estGN = useMemo(() => "GN" === agent.administration.type, [agent]);

    const estPN = useMemo(
      () => ["PN", "MI"].includes(agent.administration.type),
      [agent],
    );

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
                      href: `${window.location.origin}${estGN ? urlAvisInterventionGN : urlAvisIntervention}`,
                      target: "_self",
                      download: true,
                    }}
                  />

                  <div className="fr-grid-row fr-col-12">
                    <object
                      data={estGN ? urlAvisInterventionGN : urlAvisIntervention}
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
                      href: `${window.location.origin}${urlGuideRemiseAvis}`,
                      target: "_self",
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
                      href: `${window.location.origin}${estPN ? urlGuideDeclarationPN : urlGuideDeclaration}`,
                      target: "_self",
                      download: true,
                    }}
                  />

                  <div className="fr-grid-row fr-col-12">
                    <object
                      data={estPN ? urlGuideDeclarationPN : urlGuideDeclaration}
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
