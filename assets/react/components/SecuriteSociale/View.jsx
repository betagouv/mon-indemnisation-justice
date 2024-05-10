import React, {useState,useEffect} from 'react';
import { Select } from "@codegouvfr/react-dsfr/Select";
import ReadOnlyInput from '../ReadOnlyInput';
import { Input } from "@codegouvfr/react-dsfr/Input";
import { castNumber } from '../../utils/cast';
import { trans,
  PERSONNE_FIELD_NUMERO_SECURITE_SOCIALE
} from '../../../translator';

const SecuriteSocialeView = ({codeSS, numeroSS}) => {


  return (
    <div className="fr-grid-row">
      <div className="fr-col-9">
        <ReadOnlyInput
          label={trans(PERSONNE_FIELD_NUMERO_SECURITE_SOCIALE)}
          value={numeroSS}
        />
      </div>
      <div className="fr-col-1">
      </div>
      <div className="fr-col-2">
        <ReadOnlyInput
          label="&nbsp;"
          value={codeSS}
        />
      </div>
    </div>
  );
}

export default SecuriteSocialeView;
