import React, {useState,useEffect} from 'react';
import Referentiel from './Referentiel';
import { check_empty } from '../utils/check_state';
import { castNumber } from '../utils/cast';

import { Input } from "@codegouvfr/react-dsfr/Input";
import QualiteRequerant from "../utils/QualiteRequerant";

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
          {qualiteRequerant == 'AUT' &&
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
