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

  const [loading,setLoading]=useState(false);

  useEffect(() => {
    if(true===loading)
      return;
    setLoading(true);
  },[])
  const label = (null!==qualiteText) ? qualiteText : "J'effectue ma demande en qualité de";
  const label2 = !check_empty(precisionText) ? precisionText : "Préciser votre qualité";
  return (
    <>
      <div className="fr-grid-row">
        <div className="fr-col-4 fr-pr-md-1w">
          <Referentiel
            label={label}
            options={Object.entries(QualiteRequerant)}
            content={qualiteRequerant}
            setContent={setQualiteRequerant}
          />
        </div>
        <div className="fr-col-8">
          {qualiteRequerant == 'AUT' &&
          <Input
            label={label2}
            nativeInputProps={{
              value: precisionRequerant,
              onChange: ev=>setPrecisionRequerant(ev.target.value),
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
