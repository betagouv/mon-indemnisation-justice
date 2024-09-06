import React from 'react';
import ReadOnlyInput from '../ReadOnlyInput';

const PersonneMoraleView = ({personneMorale}) => {

  return (
    <div className="fr-grid-row">
      <div className="fr-col-12">
        <h5>Identité société</h5>
      </div>
      <div className="fr-col-5">
        <ReadOnlyInput
          label="SIREN / SIRET"
          value={personneMorale.sirenSiret}
        />
      </div>
      <div className="fr-col-1">
      </div>
      <div className="fr-col-6">
        <ReadOnlyInput
          label="Raison sociale"
          value={personneMorale.raisonSociale}
        />
      </div>
    </div>
  );
}

export default PersonneMoraleView;
