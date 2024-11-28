import React from 'react';
import Referentiel from '@/react/components/Referentiel';

import { Input } from "@codegouvfr/react-dsfr/Input";
import QualiteRequerant from "@/react/types/QualiteRequerant";

const Requerant = function({
  qualiteText=null,
  precisionText=null,
  qualiteRequerant,
  setQualiteRequerant,
  precisionRequerant,
  setPrecisionRequerant
}) {

  return (
    <>
      <div className="fr-grid-row fr-grid-row--gutters">
        <div className="fr-col-4">
          <Referentiel
            label={qualiteText ?? "Vous effectuez votre demande en qualité de"}
            options={Object.entries(QualiteRequerant)}
            content={qualiteRequerant}
            setContent={setQualiteRequerant}
          />
        </div>
        <div className="fr-col-8">
          {qualiteRequerant === QualiteRequerant.AUT &&
          <Input
            label={precisionText ?? "Précisez votre qualité"}
            nativeInputProps={{
              value: precisionRequerant,
              onChange: ev=> setPrecisionRequerant(ev.target.value),
              maxLength: 255
            }}
          />
          }
        </div>
      </div>
    </>
  );
}

export default Requerant;
