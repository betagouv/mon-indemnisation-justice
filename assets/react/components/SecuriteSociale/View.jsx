import React from 'react';
import ReadOnlyInput from '../ReadOnlyInput';

const SecuriteSocialeView = ({codeSS, numeroSS}) => {


  return (
    <div className="fr-grid-row">
      <div className="fr-col-9">
        <ReadOnlyInput
          label="Les 10 premiers chiffres de votre numéro de sécurité sociale"
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
