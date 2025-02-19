import qualiteRequerant from "@/apps/requerant/dossier/models/QualiteRequerant.ts";
import React from "react";
import Referentiel from "@/apps/requerant/dossier/components/Referentiel";

import { Input } from "@codegouvfr/react-dsfr/Input";
import QualiteRequerant from "@/apps/requerant/dossier/models/QualiteRequerant.ts";

export const Requerant = function ({
  qualiteText = null,
  precisionText = null,
  qualiteRequerant,
  setQualiteRequerant,
  precisionRequerant,
  setPrecisionRequerant,
}: {
  qualiteText?: string;
  precisionText?: string;
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
            label={qualiteText ?? "Vous effectuez votre demande en qualité de"}
            options={Object.entries(QualiteRequerant)}
            content={qualiteRequerant}
            setContent={setQualiteRequerant}
          />
        </div>
        <div className="fr-col-lg-6 fr-col-12">
          {qualiteRequerant === "AUT" && (
            <Input
              label={precisionText ?? "Précisez votre qualité"}
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
