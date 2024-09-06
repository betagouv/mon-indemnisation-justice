import React from 'react';
import ReadOnlyInput from '../ReadOnlyInput';

const AdresseView = ({adresse}) => {

  return (
    <div className="fr-grid-row">
      <div className="fr-col-12">
        <ReadOnlyInput
          label="Adresse complète"
          value={adresse.ligne1}
        />
      </div>
      <div className="fr-col-3">
        <ReadOnlyInput
          label="Code postal"
          value={adresse.codePostal}
        />
      </div>
      <div className="fr-col-1">
      </div>
      <div className="fr-col-8">
        <ReadOnlyInput
          label="Ville"
          value={adresse.localite}
        />
      </div>
    </div>
  );
}

export default AdresseView;
