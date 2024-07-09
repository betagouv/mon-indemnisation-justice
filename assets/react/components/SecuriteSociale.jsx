import React, {useState,useEffect} from 'react';
import { Select } from "@codegouvfr/react-dsfr/Select";
import { Input } from "@codegouvfr/react-dsfr/Input";
import { castNumber } from '../utils/cast';
import { trans,
  PERSONNE_FIELD_NUMERO_SECURITE_SOCIALE,
  PERSONNE_FIELD_CODE_SECURITE_SOCIALE
} from '../../translator';



const SecuriteSociale = ({codeSS, numeroSS, setCodeSS, setNumeroSS}) => {

  return (
    <div className="fr-grid-row fr-grid-row--gutters">
      <div className="fr-col-12">
        <Input
          label={trans(PERSONNE_FIELD_NUMERO_SECURITE_SOCIALE)}
          nativeInputProps={{
            value: numeroSS,
            onChange: ev => setNumeroSS(castNumber(ev.target.value)),
            maxLength: 10
          }}
        />
      </div>
      {/*
      <div className="fr-col-3">
        <Input
          label={trans(PERSONNE_FIELD_CODE_SECURITE_SOCIALE)}
          nativeInputProps={{
            value: codeSS,
            onChange: ev => setCodeSS(castNumber(ev.target.value)),
            maxLength: 2
          }}
        />
      </div>
      */}
    </div>
  );
}

export default SecuriteSociale;
