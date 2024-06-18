import React, {useState,useEffect} from 'react';
import axios from 'axios';
import PropTypes from 'prop-types';
import Referentiel from './Referentiel';
import { getStateOnEmpty,check_empty } from '../utils/check_state';
import { castNumber } from '../utils/cast';
import { fr } from "@codegouvfr/react-dsfr";
import { Table } from "@codegouvfr/react-dsfr/Table";
import { Button } from "@codegouvfr/react-dsfr/Button";
import { Select } from "@codegouvfr/react-dsfr/Select";
import { trans,
  BRIS_PORTE_FIELD_QUALITE_REPRESENTANT,
  BRIS_PORTE_FIELD_PRECISION_REPRESENTANT
} from '../../translator';
import { Input } from "@codegouvfr/react-dsfr/Input";

const Requerant = function({
  qualiteText=null,
  precisionText=null,
  qualiteRequerant,
  setQualiteRequerant,
  precisionRequerant,
  setPrecisionRequerant
}) {

  const CODE_QUALITE_REQUERANT_AUTRE = '4';
  const [loading,setLoading]=useState(false);

  useEffect(() => {
    if(true===loading)
      return;
    setLoading(true);
  },[])
  const showPrecision = (qr) => (castNumber(qr) == CODE_QUALITE_REQUERANT_AUTRE);
  const label = (null!==qualiteText) ? qualiteText : trans(BRIS_PORTE_FIELD_QUALITE_REPRESENTANT);
  const label2 = !check_empty(precisionText) ? precisionText : trans(BRIS_PORTE_FIELD_PRECISION_REPRESENTANT);
  return (
    <>
      <div className="fr-grid-row">
        <div className="fr-col-4 fr-pr-md-1w">
          <Referentiel
            label={label}
            route={Routing.generate("_api_qualite_representant_get_collection")}
            content={qualiteRequerant}
            setContent={setQualiteRequerant}
          />
        </div>
        <div className="fr-col-8">
          {showPrecision(qualiteRequerant) &&
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