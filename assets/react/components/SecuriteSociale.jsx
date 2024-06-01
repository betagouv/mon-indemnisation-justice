import React, {useState,useEffect} from 'react';
import { Select } from "@codegouvfr/react-dsfr/Select";
import { Input } from "@codegouvfr/react-dsfr/Input";
import { castNumber } from '../utils/cast';
import { trans,
  PERSONNE_FIELD_NUMERO_SECURITE_SOCIALE
} from '../../translator';



const SecuriteSociale = ({codeSS, numeroSS, setCodeSS, setNumeroSS}) => {


  return (
    <div className="fr-grid-row">
      <div className="fr-col-9 fr-pr-md-1w">
        <Input
          label={trans(PERSONNE_FIELD_NUMERO_SECURITE_SOCIALE)}
          nativeInputProps={{
            name: 'numeroSecuriteSociale',
            value: numeroSS,
            onChange: ev => setNumeroSS(castNumber(ev.target.value)),
            maxLength: 13
          }}
        />
      </div>
      <div className="fr-col-3">
        <Input
          label="&nbsp;"
          nativeInputProps={{
            name: 'codeSecuriteSociale',
            value: codeSS,
            onChange: ev => setCodeSS(castNumber(ev.target.value)),
            maxLength: 2
          }}
        />
      </div>
    </div>
  );
}

export default SecuriteSociale;
