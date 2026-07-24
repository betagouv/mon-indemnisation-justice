import { DossierApercu, Usager } from "@common/models";
import { ListeDossiers } from "@fip6/composants/dossiers/ListeDossiers.tsx";
import { createFileRoute } from "@tanstack/react-router";
import { plainToInstance } from "class-transformer";
import React, { useMemo } from "react";

export const Route = createFileRoute("/dossiers/usager/$id")({
  component: RouteComponent,
  loader: async ({ params }) => {
    const reponse = await fetch(`/api/agent/fip6/dossiers/usager/${params.id}`);
    const data = await reponse.json();

    return {
      usager: plainToInstance(Usager, data.usager),
      dossiers: plainToInstance(DossierApercu, data.dossiers as any[]),
    };
  },
});

function RouteComponent() {
  const { usager, dossiers }: { usager: Usager; dossiers: DossierApercu[] } =
    Route.useLoaderData();

  const s = useMemo(() => (dossiers.length > 1 ? "s" : ""), [usager.id]);

  return (
    <div className="fr-grid-row">
      <div className="fr-col-12">
        <h1>Dossiers déposés par {usager.nomSimple()}</h1>
      </div>
      <div className="fr-col-12">
        <ListeDossiers
          dossiers={dossiers}
          caption={`${dossiers.length} dossier${s} correspondant${s}`}
        />
      </div>
    </div>
  );
}
