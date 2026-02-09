import React from "react";
import Referentiel from "@/apps/requerant/dossier/components/Referentiel";

import { Input } from "@codegouvfr/react-dsfr/Input";
import { QualiteRequerants } from "@/apps/requerant/models/QualiteRequerant.ts";

export const Requerant = function ({
  qualiteRequerant,
  setQualiteRequerant,
  precisionRequerant,
  setPrecisionRequerant,
}: {
  qualiteRequerant: string;
  setQualiteRequerant: (qualiteRequerant: string) => void;
  precisionRequerant: string;
  setPrecisionRequerant: (precisionRequerant: string) => void;
}) {
  return (
    <>
      <div className="fr-grid-row fr-grid-row--gutters">
        <div className="fr-col-lg-6 fr-col-12">
          <Referentiel
            label="Vous effectuez votre demande en qualité de"
            options={Object.entries(QualiteRequerants)}
            content={qualiteRequerant}
            setContent={setQualiteRequerant}
          />
        </div>
        <div className="fr-col-lg-6 fr-col-12">
          {qualiteRequerant === "AUT" && (
            <Input
              label="Précisez"
              nativeInputProps={{
                value: precisionRequerant,
                onChange: (ev) => setPrecisionRequerant(ev.target.value),
                maxLength: 255,
              }}
            />
          )}
        </div>
      </div>
    </>
  );
};
