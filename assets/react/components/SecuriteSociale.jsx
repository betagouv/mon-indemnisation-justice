import React from 'react';
import { Input } from "@codegouvfr/react-dsfr/Input";
import { castNumber } from '../utils/cast';


const SecuriteSociale = ({codeSS, numeroSS, setCodeSS, setNumeroSS}) => {

  return (
    <div className="fr-grid-row fr-grid-row--gutters">
      <div className="fr-col-12">
        <Input
          label="Les 10 premiers chiffres de votre numéro de sécurité sociale"
          nativeInputProps={{
            value: numeroSS,
            onChange: ev => setNumeroSS(ev.target.value),
            maxLength: 10
          }}
        />
      </div>
      {/*
      <div className="fr-col-3">
        <Input
          label="Les 10 premiers chiffres de votre numéro de sécurité sociale"
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
