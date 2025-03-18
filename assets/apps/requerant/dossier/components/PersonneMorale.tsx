import React, { useContext } from "react";
import { Input } from "@codegouvfr/react-dsfr/Input";
import {
  DossierContext,
  PatchDossierContext,
} from "@/apps/requerant/dossier/contexts/DossierContext.ts";

const PersonneMorale = () => {
  const dossier = useContext(DossierContext);
  const patchDossier = useContext(PatchDossierContext);

  return (
    <>
      <h3>Identité de la société</h3>
      <div className="fr-grid-row fr-grid-row--gutters">
        <div className="fr-col-lg-6 fr-col-12">
          <Input
            label="Raison sociale"
            nativeInputProps={{
              value: dossier.requerant.personneMorale?.raisonSociale || "",
              onChange: (e) =>
                patchDossier({
                  requerant: {
                    personneMorale: {
                      raisonSociale: e.target.value,
                      sirenSiret:
                        dossier.requerant.personneMorale?.sirenSiret || "",
                    },
                  },
                }),
              maxLength: 255,
            }}
          />
        </div>
        <div className="fr-col-lg-6 fr-col-12">
          <Input
            label="SIREN / SIRET"
            nativeInputProps={{
              value: dossier.requerant.personneMorale?.sirenSiret || "",
              onChange: (e) =>
                patchDossier({
                  requerant: {
                    personneMorale: {
                      raisonSociale:
                        dossier.requerant.personneMorale?.raisonSociale || "",
                      sirenSiret: e.target.value,
                    },
                  },
                }),
              maxLength: 255,
            }}
          />
        </div>
      </div>
    </>
  );
};

export default PersonneMorale;
